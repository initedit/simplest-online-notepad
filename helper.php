<?php

session_start();

function get_post($key) {
    return isset($_POST[$key]) ? $_POST[$key] : null;
}

function isset_post($key) {
    return isset($_POST[$key]) ? true : false;
}

function isempty_post($key) {
    return empty($_POST[$key]) ? true : false;
}

function sanitize_filename($file) {
    $new_file = $file;
    $sanitize_file = str_replace("/", "", $new_file);
    return $sanitize_file;
}

function getSlugName() {
    $file = get_post("file");
    $filename = NULL;
    if ($file === NULL) {
        if (isset($_GET["file"])) {
            $filename = $_GET["file"];
        } else {
            $filename = $_SERVER['REQUEST_URI'];
        }
    } else {
        $filename = $file;
    }
    return $filename;
}

function get_lock_notefile($notefile) {
    return $notefile . ".hashlock";
}

function get_session($key) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
}

function set_session($key, $value) {
    $_SESSION[$key] = $value;
}

function unset_session($key) {
    unset($_SESSION[$key]);
}

function isset_session($key) {
    return isset($_SESSION[$key]) ? true : false;
}

function get_action() {
    $action = get_post("action");
    if ($action === NULL) {
        if (isset($_GET["action"])) {
            $action = $_GET["action"];
        }
    }
    return $action;
}

function encrypt($msg) {
    $masala = "asd6&876q2)!@mbxcb";
    return hash("sha256", $masala . $msg);
}

function has_note_edit_permission($lockfile) {
    if (isset_session("HASH")) {
        $permission = (get_session("HASH") == getNotePassword(NOTE_FILE_NAME)) ? true : false;
        if ($permission) {
            return $permission;
        }
    }
    if (isNoteLocked(NOTE_FILE_NAME)) {
        return false;
    } else {
        return true;
    }
}

function save_data($filepath, $d) {

    return saveNote($filepath, $d);
}

function get_notedata() {

    $notes = getNoteBySlug(NOTE_FILE_NAME);
    $emptyNote = array(
        "name" => "Untitled Document",
        "data" => null,
        "created_by" => "Anonymous",
        "created_on" => time(),
        "modified_by" => "Anonymous",
        "modified_on" => null,
        "visibility" => true,
        "empty" => true,
        "bookmark" => encrypt(time()),
        "slug" => NOTE_FILE_NAME
    );
    if (count($notes) == 0) {

        $notes[] = $emptyNote;
    }
    $data = array(
        "notes" => $notes,
        "emptyNote" => $emptyNote
    );
    return $data;
}

function get_notealldata() {

    $notes = getNoteBySlug(NOTE_FILE_NAME);
    
    return $notes;
}

function get_notefile_content($filepath) {

    return getNotePassword($filepath);
}

function download_tab_file($d) {
    //Removes the "note." from filename
    $filename = $d["name"];


    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    //Check if file exists (for debug purpose)
    $tabTxt = $d["data"];
    if (strlen($tabTxt) > 0) {
        header('Content-Length: ' . count($tabTxt));
        echo $tabTxt;
    } else {
        header('Content-Length: ' . 0);
    }
}
