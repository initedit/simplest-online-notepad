<?php
/*
 * 
 * Request Level Cache Object for optimization
 */
$MEM_CACHE = [];

function getNote($KEY, $VAL) {

    $db = new Database();
    $query = "SELECT * FROM notes WHERE $KEY=:VAL ORDER BY order_index ASC";
    $db->query($query);
    $db->bind("VAL", $VAL);
    $result = $db->resultset();
    $result = array_map(function($d) {
        $d["c"] = $d["visibility"];
        if ($d["visibility"] == "0") {
            $d["visibility"] = true;
        } else {
            $d["visibility"] = false;
        }
        unset($d["c"]);
        return $d;
    }, $result);
    return $result;
}

function getNoteWithoutData($KEY, $VAL) {
    global $MEM_CACHE;

    if (isset($MEM_CACHE[$KEY])) {
        return $MEM_CACHE[$KEY];
    }

    $db = new Database();
    $query = "SELECT `notes`.`id`,
    `notes`.`name`,
    `notes`.`slug`,
    `notes`.`status`,
    `notes`.`type`,
    `notes`.`bookmark`,
    `notes`.`visibility`,
    `notes`.`password`,
    `notes`.`parentid`,
    `notes`.`createdon`,
    `notes`.`modifiedon`,
    `notes`.`order_index` FROM notes WHERE $KEY=:VAL ORDER BY order_index ASC";
    $db->query($query);
    $db->bind("VAL", $VAL);
    $result = $db->resultset();
    $result = array_map(function($d) {
        $d["c"] = $d["visibility"];
        if ($d["visibility"] == "0") {
            $d["visibility"] = true;
        } else {
            $d["visibility"] = false;
        }
        unset($d["c"]);
        return $d;
    }, $result);
    $MEM_CACHE[$KEY] = $result;

    return $result;
}

function getNoteBySlug($slug) {
    return getNoteWithoutData("slug", $slug);
}

function getAllNoteBySlug($slug) {
    return getNote("slug", $slug);
}

function getNoteById($id) {
    $n = getNote("id", $id);
    if (count($n) > 0) {
        return $n[0];
    }

    return null;
}

function getNotePassword($slug) {
    $result = getNoteBySlug($slug);
    if (count($result) > 0) {
        $note = $result[0];
        return $note["password"];
    }
    return null;
}

function isNoteLocked($slug) {
    $result = getNoteBySlug($slug);
    if (count($result) > 0) {
        $note = $result[0];
        return $note["type"] == "public" ? false : true;
    }
    return false;
}

function isNoteViewable($slug) {
    $result = getNoteBySlug($slug);
    if (count($result) > 0) {
        $note = $result[0];
        if ($note["type"] === "private") {
            return has_note_edit_permission($slug);
        } else {
            return true;
        }
    }
    return true;
}

function setNotePassword($slug, $password, $privacy) {
    $db = new Database();
    $query = "UPDATE notes SET 
                          modifiedon=now(),
                          password=:password,
                          type=:type
                          WHERE slug=:slug";
    $db->query($query);
    $db->bind("password", $password);
    $db->bind("slug", $slug);
    $db->bind("type", $privacy);
    return $db->execute();
}

function saveNote($slug, $notes) {
    $db = new Database();
    $pwd = (NOTE_AUTHORIZED) ? get_session("HASH") : null;
    $note_type = isset_post("type") == "true" ? "private" : $pwd == null ? "public" : "protected";

    $oldNote = getNoteBySlug(NOTE_FILE_NAME);
    if (count($oldNote) > 0) {
        $note_type = $oldNote[0]["type"];
    }

    $finalNotes = [];
    try {
        $db->beginTransaction();
        $order_index = 0;
        foreach ($notes as $note) {
            $query = "";
            if (isset($note["order_index"])) {
                $order_index = $note["order_index"];
            }
            if (isset($note["deleted"])) {
                $query = "DELETE FROM notes 
                          WHERE id=:id";
                $db->query($query);
                $db->bind("id", $note["id"]);
                $db->execute();
            } else if (isset($note["id"])) {
                if (isset($note["data"])) {
                    $query = "UPDATE notes SET 
                          name=:name,
                          data=:data,
                          modifiedon=now(),
                          visibility=:visibility,
                          order_index=:order_index
                          WHERE id=:id";
                    $db->query($query);
                    $db->bind("name", $note["name"]);
                    $db->bind("data", $note["data"]);
                    $db->bind("visibility", $note["visibility"] == "true" ? 0 : 1);
                    $db->bind("id", $note["id"]);
                    $db->bind("order_index", $order_index);
                    $db->execute();
                } else {
                    $query = "UPDATE notes SET 
                          name=:name,
                          modifiedon=now(),
                          visibility=:visibility,
                          order_index=:order_index
                          WHERE id=:id";
                    $db->query($query);
                    $db->bind("name", $note["name"]);
                    $db->bind("visibility", $note["visibility"] == "true" ? 0 : 1);
                    $db->bind("id", $note["id"]);
                    $db->bind("order_index", $order_index);
                    $db->execute();
                }
                $finalNotes[] = $note;
            } else {


                $query = "INSERT INTO notes(name,data,slug,status,password,bookmark,type,parentid,modifiedon,visibility,order_index)
                        VALUES(:name,:data,:slug,:status,:password,:bookmark,:type,:parentid,:modifiedon,:visibility,:order_index)";
                $db->query($query);
                $db->bind("name", $note["name"]);
                $db->bind("data", $note["data"]);
                $db->bind("slug", $note["slug"]);
                $db->bind("status", 0);
                $db->bind("type", $note_type);
                $db->bind("bookmark", NOTE_FILE_NAME . '-' . time() . "-" . $order_index);
                $db->bind("visibility", $note["visibility"] == "true" ? 0 : 1);
                $db->bind("password", $pwd);
                $db->bind("parentid", 0);
                $db->bind("modifiedon", null);
                $db->bind("order_index", $order_index);
                $db->execute();
                $note["id"] = $db->lastInsertId();
                $note["added"] = true;
                $finalNotes[] = $note;
            }
            $order_index++;
        }

        $db->endTransaction();
    } catch (Exception $ex) {
//        file_put_contents(__DIR__ . "/log1.txt", [json_encode($notes), "\n", $ex]);
        $db->cancelTransaction();
    }
    return $finalNotes;
}

function deleteNoteBook($slug) {
    $db = new Database();
    $query = "delete from notes where slug=:slug";
    $db->query($query);
    $db->bind("slug", $slug);
    $db->execute();
    return true;
}
