<?php

include './helper/CookieManagment.php';
include './helper/SessionManagement.php';
include './helper/Database.php';
include './helper/DB_Note.php';
include './helper.php';
include './common.php';
$result = array(
    "code" => 100,
    "message" => "Unknown error"
);

$action = get_action();
if ($action === "getnote") {
    $result["data"] = get_notedata();
    $result["authorized"] = NOTE_AUTHORIZED;
    $result["isLocked"] = NOTE_LOCKED;
    $result["message"] = "Got note";
    if (!NOTE_VIEWABLE) {
        $result["data"]["notes"] = [];
        $result["code"] = 403;
        $result["message"] = "Permission Denied";
    }
} else if ($action === "gettab") {
    $id = get_post("id");
    $result["data"] = getNoteById($id);
    $result["authorized"] = NOTE_AUTHORIZED;
    $result["isLocked"] = NOTE_LOCKED;
    $result["message"] = "Got tab";
    $result["code"] = 1;
    if (!NOTE_VIEWABLE) {
        $result["data"]= NULL;
        $result["code"] = 403;
    }
} else if ($action === "download-tab") {
    $d = isset_post("data") ? get_post("data") : null;
    download_tab_file($d);
    exit(0);
} else if (NOTE_AUTHORIZED) {
    if ($action === "save" && isset_post("data")) {
        $data = get_post("data");
        $result["notes"] = save_data(NOTE_FILE_NAME, $data);
        $result["message"] = "Saved";
        $result["data"] = $data;
    } else if ($action === "lock" && isset_post("data") && !NOTE_LOCKED) {

        $isBookPrivate = get_post("private") == "true" ? "private" : "protected";
        $password = get_post("data");
        $encrypt = encrypt($password);
        setNotePassword(NOTE_FILE_NAME, $encrypt, $isBookPrivate);
        set_session("HASH", $encrypt);

        $result["message"] = "Locked";
    } else if ($action === "lock" && isset_post("data") && NOTE_LOCKED) {
        $result["message"] = "Password cannot be changed";
    } else if ($action === "unlock") {
        unset_session("HASH");
        $result["message"] = "Locked";
    } else if ($action === "delete") {

        $isDeleted = deleteNoteBook(NOTE_FILE_NAME);
        unset_session("HASH");
        if ($isDeleted) {
            $result["message"] = "Note Deleted";
            $result["code"] = 1;
        } else {
            $result["message"] = "Unable to remove note";
        }
    }else if($action=="getallnote"){
        $result["data"] = getAllNoteBySlug(NOTE_FILE_NAME);
    }
} else if ($action === "lock" && isset_post("data")) {

    $password = get_post("data");
    $encrypt = encrypt($password);
    $encrypt_saved = get_notefile_content(NOTE_FILE_NAME);

    if ($encrypt === $encrypt_saved) {
        $result["message"] = "Unlocked";
        $result["code"] = 1;
        set_session("HASH", $encrypt);
    } else {
        $result["message"] = "Wrong Password";
    }
} else {
    $result["message"] = "Locked";
}
$result["action"] = $action;

echo json_encode($result);
