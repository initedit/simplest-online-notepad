function noteEncrypt(text, passKey) {
    // An example 128-bit key (16 bytes * 8 bits/byte = 128 bits)
    var key = getNoteKey();
    if (passKey !== undefined)
    {
        key = getNormalizeKey(normalizeKey(passKey));
    }

// Convert text to bytes    
    var textBytes = aesjs.utils.utf8.toBytes(text);

// The counter is optional, and if omitted will begin at 1
    var aesCtr = new aesjs.ModeOfOperation.ctr(key, new aesjs.Counter(5));
    var encryptedBytes = aesCtr.encrypt(textBytes);

// To print or store the binary data, you may convert it to hex
    var encryptedHex = aesjs.utils.hex.fromBytes(encryptedBytes);
    console.log(encryptedHex);
    return encryptedHex;
}

function noteDecrypt(text) {
    // An example 128-bit key (16 bytes * 8 bits/byte = 128 bits)
    var key = getNoteKey();


    var encryptedHex = text;
// When ready to decrypt the hex string, convert it back to bytes
    var encryptedBytes = aesjs.utils.hex.toBytes(encryptedHex);

// The counter mode of operation maintains internal state, so to
// decrypt a new instance must be instantiated.
    var aesCtr = new aesjs.ModeOfOperation.ctr(key, new aesjs.Counter(5));
    var decryptedBytes = aesCtr.decrypt(encryptedBytes);

// Convert our bytes back into text
    var decryptedText = aesjs.utils.utf8.fromBytes(decryptedBytes);
    console.log(decryptedText);
// "Text may be any length you wish, no padding is required."

    return decryptedText;
}

function setNoteKey(pass) {
    if (typeof (Storage)) {
        pass = normalizeKey(pass);
        sessionStorage["NOTE_PASS"] = pass;
    }
}

function getNoteKey() {
    if (typeof (Storage)) {
        return getNormalizeKey(sessionStorage["NOTE_PASS"]);
    }
    return null;
}

function normalizeKey(pass) {
    while (pass.length < 16) {
        pass = "0" + pass;
    }
    pass = pass.substr(0, 16);
    return pass;
}
function getNormalizeKey(pass) {
    return    aesjs.utils.utf8.toBytes(pass);
}