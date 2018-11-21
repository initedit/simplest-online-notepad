<html>
    <head>
        <title>Note.</title>
        <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700" rel="stylesheet">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" >
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.min.css" />
        <link href="/assets/css/toast.jquery.css" rel="stylesheet">
        <link href="/assets/css/style.css?v=1.0.8" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/js/bootstrap.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="/assets/js/toast.jquery.js"></script>
        <script src="/assets/js/mousewheel.jquery.js"></script>
        <script src="/assets/js/aes-js.js"></script>
        <script src="/assets/js/encryption.index.js?v=1.0"></script>
        <script src="/assets/js/index.js?v=1.1"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <meta name="description" content="Simplest Online Notepad editor with security"/>
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
    </head>
    <body>
        <header class="main-header clearfix text-center">   
            <a href="/" class="home-icon"><i class="fa fa-home left"></i></a>
            <span class="left" id="book-name"></span>
            <span class="brand-icon">Note.</span>
            <i class="fa fa-info right" id="rightInfoBtn"></i>   
        </header>
        <div class="overlay" id="infoOverlay"></div>
        <div class="info-menu text-center" id="infoMenu">
            <i class="fa fa-close close-info" id="closeInfoMenuBtn"></i>
            <br/>
            <br/>
            <h1>Note.</h1>
            <p class="version">Version 1.0</p>
            <br/>
            <hr/>
            <p class="desc">Minimilistic online notepad editor made using PHP and DHTML,<br/> Databases used</p>

            <br/>
            <br/>

            <div class="social">

                <a href="https://twitter.com/initedit" target="new">
                    <i class="fa fa-twitter"></i>
                </a>
                <a href="https://github.com/initedit-project" target="new">
                    <i class="fa fa-github"></i>
                </a>
            </div>
            <br/>
            <p class="small">
                &copy; <?php echo date("Y"); ?>
            </p>
        </div>
        <main>
            <section id="sidebar-left" class="sidebar marker-visible">
                <div class="menu-item" id="bookNoteBtn">
                    <i class="fa fa-book icon"></i> 
                    <span class="title">
                        <span class="hint">View Book Index(ALT+B)</span>
                    </span>
                </div>
                <div class="menu-item" id="newNoteBtn">
                    <i class="fa fa-plus icon"></i> 
                    <span class="title">
                        <span class="hint">New Note(ALT+N)</span>
                    </span>
                </div>
                <div class="menu-item" id="saveNoteBtn">
                    <i class="fa fa-save icon"></i>
                    <span class="title">
                        <span class="hint">Save(CTRL+S)</span>
                    </span>
                </div>

                <div class="menu-item" id="editNoteBtn">
                    <i class="fa fa-pencil icon"></i>
                    <span class="title">
                        <span class="hint">Edit(ALT+E)</span>
                    </span>
                </div>
                <div class="menu-item" id="lockNoteBtn">
                    <i class="fa fa-lock icon"></i>
                    <span class="title">
                        <span class="hint">Unlock(ALT+U)</span>
                    </span>
                </div>

                <div class="menu-item" id="unlockAltNoteBtn">
                    <i class="fa fa-unlock-alt icon"></i>
                    <span class="title">
                        <span class="hint">Lock(ALT+U)</span>
                    </span>
                </div>

                <div class="menu-item" id="withoutLockNoteBtn">
                    <i class="fa fa-unlock icon"></i>
                    <span class="title">
                        <span class="hint">Lock(ALT+U)</span>
                    </span>
                </div>

                <div class="menu-item" id="linkNoteBtn">
                    <i class="fa fa-link icon"></i>
                    <span class="title">
                        <span class="hint">Copy URL(ALT+L)</span>
                    </span>
                </div>
                <div class="menu-item" id="clipboardNoteBtn">
                    <i class="fa fa-clipboard icon"></i>
                    <span class="title">
                        <span class="hint">Copy(ALT+C)</span>
                    </span>
                </div>

                <div class="menu-item" id="printNoteBtn">
                    <i class="fa fa-print icon"></i>
                    <span class="title">
                        <span class="hint">Print</span>
                    </span>
                </div>

                <div class="menu-item" id="deleteNoteBtn">
                    <i class="fa fa-trash icon"></i>
                    <span class="title">
                        <span class="hint">Delete(ALT+DEL)</span>
                    </span>
                </div>

                <div class="menu-item" id="downloadNoteBtn">
                    <i class="fa fa-download icon"></i>
                    <span class="title">
                        <span class="hint">Download(ALT+D)</span>
                    </span>
                </div>
            </section>
            <section id="content" class="content">
                <div class="note-tabs-scroll-container">
                    <div class="note-tabs-scroll">
                        <div id="note-tabs-container">
                            <div id="note-tabs">

                            </div>

                            <div id="tab-template" class="none">
                                <div class="tab">
                                    <span class="unsaved-indicator">*</span>
                                    <input type="text" class="tab-title">
                                    <i class="fa fa-close"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="note-tab-add" class="note-tab-add">
                        <i class="fa fa-plus"></i>
                    </div>
                </div>

                <textarea id="note-data" 
                          class="note-textarea"
                          placeholder="type your note"
                          spellcheck="false"
                          
                          ></textarea>
                <input type="text" class="note-hidden" id="noteHiddenUrl"/>
                <textarea class="note-hidden" id="noteHiddenData"></textarea>
            </section>

            <!--Book Index Dialog-->
            <div class="modal fade" id="bookIndexDialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Book's Index</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>
                            <div class="form-group">
                                <input type="text"  class="form-control" id="note-search-input" placeholder="search notes"/>
                            </div>
                            </p>
                            <div class="note-list list-group" id="note-list">

                            </div>
                            <div id="note-list-tenplate" class="none">     
                                <div class="list-group-item list-group-item-action item-index">
                                    <span class="pull-left">
                                        <span class="btn btn-xs btn-default">
                                            <i class="fa fa-sort"></i>
                                        </span>
                                    </span>
                                    <span class="name d-inline-block text-truncate"></span>
                                    <span class="pull-right">
                                        <span class="btn btn-xs btn-default">
                                            <i class="fa fa-trash"></i>
                                        </span>
                                        <span class="btn btn-xs btn-default">
                                            <i class="fa fa-download"></i>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>    

            <!--Delete Note Dialog-->
            <div class="modal fade" id="deleteNoteDialog">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Delete all notes?</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>You won't be able to recover this book.</p>
                        </div> 
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" onclick="deleteNote()">Delete</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>    

            <!--Qummand Note Dialog-->
            <div class="modal fade" id="qummandNoteDialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <!--<div class="form-group">-->
                            <input type="text"  class="form-control" id="note-qummand-input" placeholder="type command"/>
                            <!--</div>-->
                        </div>
                    </div>
                </div>
            </div>    

            <!--Lock Note Dialog-->
            <div class="modal fade" id="bootDialog">
                <div class="modal-dialog  modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Password</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="lockNoteDialogForm">
                                <div class="form-group">
                                    <input type="password" id="dialogPassword" class="form-control" placeholder="Enter Password"/>
                                </div>
                                <div class="form-group d-none" id="makeBookPrivateContainer">
                                    <div class="row">
                                        <div class="col-sm-8">Make this book private</div>
                                        <div class="col-sm-4">

                                            <label class="switch">
                                                <input type="checkbox" id="makeBookPrivate">
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </div>


                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="lockNote()">Set</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>            

            <!--New Note Dialog-->
            <div class="modal fade" id="newNoteDialog">
                <div class="modal-dialog  modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">New Note</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="newNoteDialogForm">

                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon"></span>
                                    </div>
                                    <input type="text" class="form-control" id="basic-url" aria-describedby="basic-addon">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="gotoNewNote()">Create</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar"></div>
            </div>
        </main>
        <footer></footer>
    </body>
</html>