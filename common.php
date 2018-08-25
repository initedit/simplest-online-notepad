<?php
$file = getSlugName();
define("NOTE_FILE_NAME", sanitize_filename($file));

/*
 * 
 * Setup Notes Property
 * 
 */

define("NOTE_LOCKED", isNoteLocked(NOTE_FILE_NAME));
define("NOTE_VIEWABLE", isNoteViewable(NOTE_FILE_NAME));
define("NOTE_AUTHORIZED", has_note_edit_permission(NOTE_FILE_NAME));
