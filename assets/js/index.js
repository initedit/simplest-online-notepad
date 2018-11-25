$(document).ready(initNote);
var config = {
    noteData: null
};

/**
 * Initializes Application with all event handling 
 * and loading initial state data
 * 
 * @returns {undefined}
 * 
 */
function initNote() {
    setupAjaxHandler();
    $("#newNoteBtn").click(showNewNoteDialog);
    $("#bookNoteBtn").click(showBookIndexDialog);
    $("#saveNoteBtn").click(saveNote);
    $("#lockNoteBtn").click(lockNoteDialog);
    $("#unlockAltNoteBtn").click(unlockNote);
    $("#deleteNoteBtn").click(deleteNoteDialog);
    $("#linkNoteBtn").click(copyNoteLinkToClipboard);
    $("#clipboardNoteBtn").click(copyNoteToClipboard);
    $("#rightInfoBtn").click(showInfo);
    $("#leftMenuBtn").click(toggleLeftMenu);
    $("#downloadNoteBtn").click(downloadCurrentNoteTab);
    $("#editNoteBtn,#withoutLockNoteBtn").click(lockNoteDialog);
    $("#printNoteBtn").click(printNoteCurretTab);

    $("#bootDialog").on("shown.bs.modal", function () {
        $(this).find("#dialogPassword").val("").focus();
    });
    $("#newNoteDialog").on("shown.bs.modal", function () {
        $(this).find("#basic-url").val("").focus();
    });
    $("#bookIndexDialog").on("shown.bs.modal", function () {
        $(this).find("#note-search-input").val("").focus();
    });
    $("#basic-addon").text(getDomainName());
    $("#infoOverlay,#closeInfoMenuBtn").click(hideInfo);

    $("#infoMenu").click(function (e) {
        e.stopPropagation();
    });
    $(window).on("keydown", shortcutMap);

    $("#lockNoteDialogForm").on("submit", function (e) {
        e.preventDefault();
        lockNote();
        return false;
    });
    $("#newNoteDialogForm").on("submit", function (e) {
        e.preventDefault();
        gotoNewNote();
        return false;
    });

    var $noteData = $("#note-data");

    $noteData.on("input", (function () {
        var note = config.activeNote;
        delete note.empty;
        updateNoteTabModifiedUI(note);
        note.modifiedData = $(this).val();
    }));
    $("#note-tab-add").click(addNewNoteTab);

    $("#note-search-input").on("input", function () {
        var strSearchQuery = $(this).val().toLowerCase();
        var $noteTabIndex = $("#note-list").find(".item-index");
        for (var tabIndex = 0; tabIndex < $noteTabIndex.length; tabIndex++) {

            var $tab = $($noteTabIndex.get(tabIndex));
            var name = $tab.find(".name").html().toLowerCase().trim();
            if (name.lastIndexOf(strSearchQuery, 0) === 0) {
                $tab.show();
            } else {
                $tab.hide();
            }

        }
    });

    //Handle Horizontal Scroll On Tabs
    $('.note-tabs-scroll').on("mousewheel", function (e, delta) {
        this.scrollLeft -= (delta * 40);
        e.preventDefault();
    });

    $("#book-name").click(copyNoteLinkToClipboard);
    $(window).on("resize", updateNewNoteTabButton);

    /**
     * Initial loading of notes
     * 
     */
    fetchNote(window.location.pathname);
}

function setupAjaxHandler() {
    var shouldAnimate = 0;
    var animateMe = function (targetElement, speed) {
        $(targetElement).css({left: '-200px'});
        $(targetElement).animate(
                {
                    'left': $(document).width() + 200
                },
                {
                    duration: speed,
                    complete: function () {
                        if (shouldAnimate > 0) {
                            animateMe(this, speed);
                        }
                    }
                }
        );
    };
    $(document).ajaxSend(function () {
        shouldAnimate++;
        if (shouldAnimate === 1)
            animateMe($(".progress-bar"), 1000);

        $(".progress-bar-container").show();
    });
    $(document).ajaxComplete(function () {
        shouldAnimate--;
        $(".progress-bar-container").hide();
    });
}

/**
 * Helper function to open print dialog (more specifically window)
 * 
 * @returns {undefined}
 * 
 */
function printNoteCurretTab() {
    var $parent = $("#note-data");
    var prtContent = $parent.val();

    var width = $parent.width();
    var height = $parent.height();
    var WinPrint = window.open('', '', 'left=0,top=0,width=' + width + ',height=' + height + ',toolbar=0,scrollbars=0,status=0');
    WinPrint.document.write("<pre>");
    WinPrint.document.write(prtContent);
    WinPrint.document.write("</pre>");
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    WinPrint.close();
}


/**
 * 
 * Set ups Sortable List for reordering note tabs
 * 
 * @returns {undefined}
 * 
 */
function setupReordering() {
    $("#note-list").sortable({
        revert: true,
//        handle: ".fa-sort",
        helper: 'clone',
        start: function (e, ui) {
            // creates a temporary attribute on the element with the old index
            $(this).attr('data-previndex', ui.item.index());
        },
        update: function (event, ui) {
            var oldIndex = $(this).attr('data-previndex');

            // new Index because the ui.item is the node and the visual element has been reordered
            var newIndex = ui.item.index();
            config.noteData.notes.splice(newIndex, 0, config.noteData.notes.splice(oldIndex, 1)[0]);
            for (var i = 0; i < config.noteData.notes.length; i++)
            {
                config.noteData.notes[i].order_index = i+1;
                config.noteData.notes[i].modified = true;
            }

            setupoNotepadDataUI(config.noteData);
        }
    });
    $("ul, li").disableSelection();
}

/**
 * 
 * Shows book index dialog user interface
 * 
 * @returns {undefined}
 * 
 */

function showBookIndexDialog() {
    $("#bookIndexDialog").modal("show");
    $("#note-list").empty();
    for (var noteIndex in config.noteData.notes) {
        (function () {
            var note = config.noteData.notes[noteIndex];
            if (!note.deleted) {
                var $noteListItem = $($("#note-list-tenplate").html());
                $noteListItem.find(".name").html(note.name);
                $noteListItem.find(".fa-trash").click(function () {
                    note.deleted = true;
                    $noteListItem.remove();
                    $("#" + note.tabid).remove();
                    setDefaultNoteVisible();
                    updateNewNoteTabButton();
                });
                $noteListItem.find(".fa-download").click(function (e) {
                    e.stopPropagation();
                    downloadNoteTab(note);
                });
                $noteListItem.find(".name").click(function () {
                    if (isNoteVisible(note)) {

                    } else {
                        note.visibility = true;
                        addNoteToUI(note);
                    }
                    setActiveNoteTab(note);
                    $("#bookIndexDialog").modal("hide");
                });
                $("#note-list").append($noteListItem);
            }
        }());
    }
    setupReordering();
}


function addNewNoteTab() {
    var note = JSON.parse(JSON.stringify(config.noteData.emptyNote));
    note.bookmark = note.bookmark + "-" + (config.noteData.notes.length + 1);
    note.order_index = getMaxOrderNumber();
    config.noteData.notes.push(note);
    setupoNotepadDataUI(config.noteData);
    setActiveNoteTab(note);

}

function getNoteDataFromUI() {
    return getNotepadDataUI();
}

function toggleLeftMenu() {
    var $menu = $("#sidebar-left");
    if ($menu.hasClass("marker-visible")) {
        $menu.animate({
            left: "-100%"
        }, "fast");
        $menu.removeClass("marker-visible");
    } else {
        $menu.animate({
            left: "0%"
        }, "fast");
        $menu.addClass("marker-visible");
    }
}
function toggleInfoMenu() {
    if ($("#infoOverlay").is(":visible")) {
        hideInfo();
    } else {
        showInfo();
    }
}

function showInfo() {
    $("#infoOverlay").fadeIn(300, function () {
        $("#infoMenu").animate({
            right: "0%"
        }, "fast");
    });
}
function hideInfo() {

    $("#infoMenu").animate({
        right: "-100%"
    }, "fast", function () {
        $("#infoOverlay").fadeOut(300);
    });
}

function copyNoteLinkToClipboard() {
    $("#noteHiddenUrl").val(window.location.href);
    $("#noteHiddenUrl").get(0).select();
    document.execCommand("Copy",false,null);
    window.getSelection().removeAllRanges();
    showMessage("URL Copied");
}

function copyNoteToClipboard() {
    $("#noteHiddenData").val($("#note-data").val());
    $("#noteHiddenData").get(0).select();
    document.execCommand("Copy",false,null);
    window.getSelection().removeAllRanges();
    showMessage("Copied");
}

function deleteNoteDialog() {
    $("#deleteNoteDialog").modal("show");
}

function deleteNote() {

    var data = {
        action: "delete",
        file: window.location.pathname
    };

    $.ajax({
        url: "/ajax.php",
        type: "POST",
        data: data,
        dataType: "json",
        success: function (data) {
            //console.log(data, "gotoNewNote");
            if (data.code === 1) {
                var $noteData = $("#note-data");
                $noteData.val("");
                refreshNote();
                $("#deleteNoteDialog").modal("hide");
            }
            showMessage(data.message);
        },
        error: ajaxError
    });
}


function handleEmptyVisibility() {
    var hasNoteVisible = false;
    for (var noteIndex in config.noteData.notes) {
        var note = config.noteData.notes[noteIndex];
        hasNoteVisible = hasNoteVisible || isNoteVisible(note);
    }
    if (!hasNoteVisible) {
        addNewNoteTab();
    }
}

function setDefaultNoteVisible() {
    handleEmptyVisibility();
    var visibilityIndex = 0;
    var activeDefaultActiveIndex = parseInt(getDefaultHash());
    if (isNaN(activeDefaultActiveIndex))
        activeDefaultActiveIndex = 1;

    if (activeDefaultActiveIndex <= 0 || activeDefaultActiveIndex > config.noteData.notes.length)
        activeDefaultActiveIndex = 1;

    for (var noteIndex in config.noteData.notes) {
        var note = config.noteData.notes[noteIndex];
        if (isNoteVisible(note)) {
            visibilityIndex++;
            if (visibilityIndex == activeDefaultActiveIndex) {
                setActiveNoteTab(note);
                return;
            }
        }
    }

}

function updateNoteTabModifiedUI(note) {
    var $tab = $("#" + note.tabid);
    if (isNoteModified(note)) {
        $tab.addClass("modified");
    } else {
        $tab.removeClass("modified");
    }

}

function isNoteVisible(note) {
    if (note.deleted) {
        return false;
    }
    return  note.visibility === "true" ? true : note.visibility === true ? true : false;
}

function getNoteTitle(note) {
    return note.modifiedName !== undefined ? note.modifiedName : note.name;
}

function addNoteToUI(note) {
    var $noteTabs = $("#note-tabs");
    if (isNoteVisible(note)) {
        var $tab = $($("#tab-template").html());
        $tab.find(".tab-title").val(getNoteTitle(note))
        $tab.data("note", note);
        $noteTabs.append($tab);
        note.tabid = "tab_" + $tab.parent().find(".tab").length;
        $tab.attr("id", note.tabid);
        updateNoteTabModifiedUI(note);
        $tab.find(".fa-close").click(function () {

            if (note.empty === true && $noteTabs.find(".tab").length === 1) {
                return;
            } else if (note.empty === true) {
                //Remove Notes Do not add it when its empty
                var index = config.noteData.notes.indexOf(note);
                config.noteData.notes.splice(index, 1);
            }

            var _note = $tab.prev(".tab").data("note");
            if (_note === undefined) {
                _note = $tab.next(".tab").data("note");
            }
            note.visibility = false;
            $tab.remove();
            if (_note !== undefined && isNoteVisible(_note)) {
                setActiveNoteTab(_note)
            } else {
                setDefaultNoteVisible();
            }
            updateNewNoteTabButton();
        });
        if (note.empty) {
            $tab.find(".tab-title").get(0).select();
        } else {
            $tab.find(".tab-title").attr("disabled", true);
            $("#note-data").focus();
        }
        $tab.on("click", function () {
            setActiveNoteTab(note);
        });
        $tab.on("dblclick", function () {
            $tab.find(".tab-title").attr("disabled", false);
            $tab.find(".tab-title").focus().select();
        });
        $tab.find(".tab-title").on("focus", function () {
            setActiveNoteTab(note);
        });
        $tab.find(".tab-title").on("blur", function () {
            $(this).attr("disabled", true);
        });
        $tab.find(".tab-title").on("input", function (e) {
            note.modifiedName = $(this).val();
            delete note.empty;
            updateNoteTabModifiedUI(note);
        });
        $tab.find(".tab-title").on("keyup", function (e) {

            if (e.keyCode === 13) {
                $("#note-data").focus();
            }
        });
        $tab.mousedown(function (e) {
            if (e.which === 2) {
                closeNoteTab(note)
            }
            return true;
        });
    }
    updateNewNoteTabButton();
}

function updateNewNoteTabButton() {
    var maxWidth = $(".note-tabs-scroll").width();
    var width = $("#note-tabs").outerWidth();
    var btnWidth = $("#note-tab-add").outerWidth();
    if (width + btnWidth > maxWidth) {
        $("#note-tab-add").css({
            top: 0,
            right: 0,
        });
    } else {
        $("#note-tab-add").css({
            top: 0,
            right: maxWidth - width - btnWidth,
        });
    }


}

function updateAllTabs() {
    for (var noteIndex in config.noteData.notes) {
        var note = config.noteData.notes[noteIndex];
        updateNoteTabModifiedUI(note);
    }
}

function setupoNotepadDataUI(data) {

    var $noteTabs = $("#note-tabs");
    $noteTabs.empty();
    config.noteData = data;
    notes_looper(function (note) {
        if (!note.deleted) {
            addNoteToUI(note);
        }
    });
    setDefaultNoteVisible();
}

function setActiveNoteTab(note) {
    if (note.data === undefined) {
        var data = {
            action: "gettab",
            id: note.id
        }
        $.ajax({
            url: "/ajax.php",
            type: "POST",
            data: data,
            dataType: "json",
            success: function (data) {
                if (data.code === 1) {
                    if (isNotesPrivate()) {
                        note.data = noteDecrypt(data.data.data);

                    } else {
                        note.data = data.data.data;
                    }
                    setActiveNoteTabUI(note);
                }
            },
            error: ajaxError
        });
    } else {
        setActiveNoteTabUI(note);
    }

}

function setActiveNoteTabUI(note) {

    var $noteData = $("#note-data");
    var $noteTabs = $("#note-tabs");
    $noteTabs.find(".tab").removeClass("selected");
    if (note.modifiedData !== undefined) {
        $noteData.val(note.modifiedData);
    } else {
        $noteData.val(note.data);
    }
    var noteIndex = getVisibleNoteOrderIndex(note);

    window.location.hash = "#" + noteIndex;
    $noteTabs.find("#" + note.tabid).addClass("selected");
    var offset = $noteTabs.find("#" + note.tabid).offset();
    var offsetContainer = $noteTabs.offset();
    //console.log(offset, $noteTabs.find("#" + note.tabid));
    var scrollbarOffset = $(".note-tabs-scroll").offset();
    if (scrollbarOffset.left < offset.left) {
        $(".note-tabs-scroll").stop().animate({
            'scrollLeft': offset.left - offsetContainer.left
        }, 200);
    } else {
        $(".note-tabs-scroll").stop().animate({
            'scrollLeft': offset.left - offsetContainer.left
        }, 200);
    }
    config.activeNote = note;
}

function getNotepadDataUI() {

    var notes = [];
    for (var noteIndex = 0; noteIndex < config.noteData.notes.length; noteIndex++) {
        var note = config.noteData.notes[noteIndex];
        if (isNoteModified(note)) {
            if (note.modifiedData !== undefined) {
                note.data = note.modifiedData;
            }
            if (note.modifiedName !== undefined) {
                note.name = note.modifiedName;
            }

            delete note.modifiedName;
            delete note.modifiedData;
            delete note.empty;
            delete note.modified;
            if (isNotesPrivate()) {
                note.data = noteEncrypt(note.data);
                note.name = noteEncrypt(note.name);
            }
            notes.push(note);
        }
    }
    return notes;
}

function isNotesPrivate() {

    if (config.noteData && config.noteData.notes) {
        for (var noteIndex = 0; noteIndex < config.noteData.notes.length; noteIndex++) {
            var note = config.noteData.notes[noteIndex];
            if (note.type === "private") {
                return true;
            }
        }
    }
    return false;
}

function focusCurrentTabTitle() {
    $(".tab.selected .tab-title").attr("disabled", false);
    $(".tab.selected .tab-title").focus();
}
function focusRenameCurrentTabTitle() {
    $(".tab.selected .tab-title").attr("disabled", false);
    $(".tab.selected .tab-title").focus().select();
}

function closeCurrentTab() {
    $(".tab.selected .fa-close").click();
}
function closeNoteTab(note) {
    $("#" + note.tabid).find(".fa-close").click();
}

function isNoteModified(note) {
    return note.modifiedName !== undefined || note.modifiedData !== undefined || note.deleted !== undefined || note.modified !== undefined;
}


function fetchNote(url) {
    config.noteData = null;
    var data = {
        action: "getnote",
        file: url
    };
    $("#book-name").html(url);
    $.ajax({
        url: "/ajax.php",
        type: "POST",
        data: data,
        dataType: "json",
        success: function (data) {
            var $noteData = $("#note-data");
            if (data.code !== 403) {
                config.noteData = data.data;
                if (isNotesPrivate())
                {
                    array_looper(data.data.notes, function (note) {
                        note.name = noteDecrypt(note.name);
                    });
                }
                setupoNotepadDataUI(data.data);
            } else {
                setupoNotepadDataUI(data.data);
                lockNoteDialog();
            }
            if (!data.isLocked) {
                $("#lockNoteBtn").hide();
                $("#unlockAltNoteBtn").hide();
                $("#withoutLockNoteBtn").show();
            } else if (data.authorized) {
                $("#lockNoteBtn").hide();
                $("#unlockAltNoteBtn").show();
                $("#withoutLockNoteBtn").hide();
            } else {
                $("#lockNoteBtn").show();
                $("#unlockAltNoteBtn").hide();
                $("#withoutLockNoteBtn").hide();
            }
            if (data.authorized || !data.isLocked) {
                $noteData.attr("disabled", false);
                $("#saveNoteBtn").show();
                $("#deleteNoteBtn").show();
                $("#editNoteBtn").hide();
            } else {
                $noteData.attr("disabled", true);
                $("#saveNoteBtn").hide();
                $("#deleteNoteBtn").hide();
                $("#editNoteBtn").show();
            }
            $("#clipboardNoteBtn,#linkNoteBtn,#newNoteBtn,#downloadNoteBtn,#bookNoteBtn,#printNoteBtn").show();
            $noteData.focus();
        },
        error: ajaxError
    });
}

function getDomainName() {
    return window.location.protocol + "//" + window.location.hostname + "/";
}

function gotoNewNote() {
    var val = $("#basic-url").val();
    var state = {};
    state.refresh = true;
    window.history.pushState(state, "", "/" + val);

    fetchNote("/" + val);
    $("#newNoteDialog").modal("hide");
}
window.onpopstate = function (e) {
    if (e.state && e.state.refresh)
        refreshNote();
};


function showNewNoteDialog() {
    var $modal = $("#newNoteDialog").modal();
    $modal.show();
}

function lockNoteDialog() {
    if ($("#withoutLockNoteBtn").is(":visible")) {
        $("#makeBookPrivateContainer").removeClass("d-none");
    } else {
        $("#makeBookPrivateContainer").addClass("d-none");
    }
    var $modal = $("#bootDialog").modal();
    $modal.show();
}

function refreshNote() {
    fetchNote(window.location.pathname);
}

function unlockNote() {

    var data = {
        action: "unlock",
        file: window.location.pathname
    };

    $.ajax({
        url: "/ajax.php",
        type: "POST",
        data: data,
        dataType: "json",
        success: function (data) {
            showMessage(data.message);
            refreshNote();
        },
        error: ajaxError
    });


}

function ajaxError(msg) {
    $("body").append(msg.responseText);
}
function lockNote() {
    var noteData = $("#dialogPassword").val();
    var isBookPrivate = $("#makeBookPrivate").is(":checked");

    if (noteData.length === 0)
        return;

    noteData = noteEncrypt(window.location.pathname, noteData)

    var data = {
        data: noteData,
        action: "lock",
        file: window.location.pathname,
        private: isBookPrivate
    };

    var ajaxSaveNote = $.ajax({
        url: "/ajax.php",
        type: "POST",
        data: data,
        dataType: "json",
        success: function (data) {
            setNoteKey(noteData);
            if (isBookPrivate)
            {
                reloadAllData();
            } else {
                refreshNote();
            }
            showMessage(data.message);
        },
        error: function (msg) {
            $("body").append(msg.responseText);
        }
    });
    ajaxSaveNote.done(function () {
        $("#bootDialog").modal("hide");
        $("#makeBookPrivate").prop("checked", false);
    });

}

function reloadAllData() {
    var data = {
        action: "getallnote",
        file: window.location.pathname,
    };

    var ajaxSaveNote = $.ajax({
        url: "/ajax.php",
        type: "POST",
        data: data,
        dataType: "json",
        success: function (data) {
            //console.log(data, data.data, data.data.length, "->Key");
            if (data.data)
            {
                for (var i = 0; i < data.data.length; i++) {
                    config.noteData.notes[i].data = noteEncrypt(data.data[i].data);
                    config.noteData.notes[i].name = noteEncrypt(data.data[i].name);

                    config.noteData.notes[i].modified = true;
                    //console.log(config.noteData.notes[i].data);
                }
            }
            saveNote(refreshNote);


        },
        error: function (msg) {
            //console.log(msg);
            $("body").append(msg.responseText);
        }
    });
    ajaxSaveNote.done(function () {
        //console.log("Lock Completed");
        $("#bootDialog").modal("hide");
    });

}
function saveNote(callback) {
    var noteData = getNoteDataFromUI();

    if (noteData.length === 0) {
        showMessage("Nothing to save");
        return;
    }

    for (var i = 0; i < noteData.length; i++)
    {
        var n = noteData[i];
        if (n.id === undefined) {
            n.dummyId = i;
        }
    }

    var type = $("#makeBookPrivate").prop("checked");

    var data = {
        data: noteData,
        action: "save",
        file: window.location.pathname,
        type: type
    };

    $.ajax({
        url: "/ajax.php",
        type: "POST",
        data: data,
        dataType: "json",
        success: function (data) {
            if (data.notes) {
                for (var i = 0; i < noteData.length; i++)
                {
                    var n = noteData[i];
                    if (n.dummyId !== undefined) {
                        n.id = data.notes[i].id;
                        n.bookmark = data.notes[i].bookmark;
                        delete n.dummyId;
                    }
                }
            }
            if (callback) {
                callback();
            } else {
                showMessage(data.message);
                updateAllTabs();
            }
        },
        error: function (msg) {
            //console.log(msg);
            $("body").append(msg.responseText);
        }
    });
}



function downloadNoteTab(tabData) {
    var tabBlog = new Blob([tabData.data], {
        type: 'text/plain'
    });
    var a = document.createElement('a');
    var url = window.URL.createObjectURL(tabBlog);
    a.href = url;
    a.download = tabData.name + ".txt";
    a.click();
    window.URL.revokeObjectURL(url);
}
function downloadCurrentNoteTab() {
    if (config.activeNote !== undefined) {
        downloadNoteTab(config.activeNote);
    } else {
        showMessage("Select Note");
    }
}

function setActiveTabByNumber(index) {
    var _index = 0;
    notes_looper(function (note) {
        if (isNoteVisible(note)) {
            _index++;
            if (_index === index) {
                setActiveNoteTab(note);
            }
        }
    });
}


function showMessage(msg) {
    msg = "<div class='toast-msg'>" + msg + "</div>";
    $.toast({
        text: msg,
        position: 'bottom-center',
        loader: false,
        stack: false,
        allowToastClose: false,
        textAlign: 'center',
        loaderBg: '#9EC600'
    });
}

/**
 * Helper method to loop data
 * 
 * @param {Array} arr
 * @param {function} callback
 * @returns undefined
 * 
 */
function array_looper(arr, callback) {
    if (arr) {
        for (var i = 0; i < arr.length; i++)
        {
            callback(arr[i], i);
        }
    }
}

/**
 * 
 * @param {function} callback
 * @returns {Boolean}
 * 
 */

function notes_looper(callback) {
    array_looper(config.noteData.notes, callback);
}

function shortcutMap(jqueryEvent) {
    var e = jqueryEvent.originalEvent;
    if (e.ctrlKey && e.altKey) {

    } else if (e.ctrlKey) {
        if (e.code === "KeyS") {
            saveNote();
            e.preventDefault();
            return false;
        }
    } else if (e.altKey) {

        if (e.keyCode >= 48 && e.keyCode <= 57) {
            var keyNumber = e.keyCode - 48;
            if (keyNumber === 0) {
                keyNumber = 10;
            }

            setActiveTabByNumber(keyNumber);
        } else if (e.code === "KeyC") {
            copyNoteToClipboard();
            e.preventDefault();
            return false;
        } else if (e.code === "CopyLink") {
            //TODO : Map Key for this function
            copyNoteLinkToClipboard();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyL") {
            if ($("#unlockAltNoteBtn").is(":visible")) {
                unlockNote();
            } else if ($("#withoutLockNoteBtn").is(":visible")) {
                lockNoteDialog();
            }
            e.preventDefault();
            return false;
        } else if (e.code === "KeyB") {
            showBookIndexDialog();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyN") {
//            addNewNoteTab();
            showNewNoteDialog();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyT") {
            addNewNoteTab();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyS") {
            saveNote();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyD") {
            downloadCurrentNoteTab();
            e.preventDefault();
            return false;
        } else if (e.code === "Delete") {
            deleteNoteDialog();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyE") {
            $("#note-data").focus();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyR") {
            focusRenameCurrentTabTitle();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyW") {
            closeCurrentTab();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyS") {
            saveNote();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyI") {
            toggleInfoMenu();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyQ") {
            showQummandDialog();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyP") {
            printNoteCurretTab();
            e.preventDefault();
            return false;
        } else if (e.code === "KeyU" || e.code === "KeyE") {
            if ($("#lockNoteBtn").is(":visible")) {
                lockNoteDialog();
            } else if ($("#unlockAltNoteBtn").is(":visible")) {
//                unlockNote();
            }
            e.preventDefault();
            return false;
        }
    }
}

function getMaxOrderNumber() {
    var o = 0;
    notes_looper(function (note) {
        if (parseInt(note.order_index) > o)
        {
            o = parseInt(note.order_index);
        }
    });
    if(isNaN(o)){
        try{
            o = parseInt(o);
        }catch{
            o = 0;
        }
    }
    return o + 1;
}

function getDefaultHash(){
  var hash = window.location.hash.replace("#", "");
  if(hash==undefined || hash.length==0){
      return 1;
  }
  return hash;
}

function getVisibleNoteOrderIndex(note){
    var activeIndex = 0;
    for(var i in config.noteData.notes){
        var n = config.noteData.notes[i];
        if(isNoteVisible(n)){
            activeIndex++;
        }
        if(n===note){
            return activeIndex;
        }
    }
    note.order_index = getMaxOrderNumber();
    return note.order_index;
}