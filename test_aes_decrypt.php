<?php
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function sha256($data) {
    return hash('sha256', $data, true);
}

function md5Hash($data) {
    return md5($data, true);
}

function pkcs7Unpadding($data) {
    $length = strlen($data);
    $padding = ord($data[$length - 1]);
    return substr($data, 0, $length - $padding);
}

function aesCbcDecrypt($data, $key, $iv) {
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $decrypted;
}

function comicApiDecryptV1($content) {
    // Base64 decode the content
    $contentByte = base64UrlDecode($content);

    // The first 16 bytes are the key
    $key = substr($contentByte, 0, 16);

    // Get the AES key using SHA256
    $aesKey = sha256($key);

    // The encrypted content is in the middle
    $encrypt = substr($contentByte, 16, -16);

    // The last 16 bytes are the IV
    $ivStr = substr($contentByte, -16);
    
    // Get the MD5 of the IV
    $ivMd5 = md5Hash($ivStr);
        
    // Prepare the IV for decryption
    $iv = '';
    for ($i = 0; $i < 16; $i++) {
        $item = (~(ord($ivMd5[$i]) ^ ord($ivStr[$i]))) & 0xFF;
        $iv .= chr($item);
    }

    // AES/CBC/PKCS5Padding decryption
    $decrypt = aesCbcDecrypt($encrypt, $aesKey, $iv);
        
    // Unpadding the decrypted content
    return pkcs7Unpadding($decrypt);
}

// Example usage
$content = "M0ZBU1dCWUQxRzJKV0dSWUviyzj7PxAJwFm3gJ3QppXNyVvP6rXsJTTXmtOIxTEMYoe9bnqKeuXCaejGrtgAN1c7eEt7sEvPdx9xvdUo4t1YifD1RhUTWtSOn/YFc1EV64doZYUzZjlUhEDtwpmKx/1xdi1pcmNOZKJhZNRhMF2l87W9t5DQ9P2KeC0GBg/tIaxRDXZdk5lQjieUM/KzlheH5ZxVPpSxa8NK52ywGbB3DLdBNahlpSU/b6+Mt+2kTMwwE78UpCpDOhXnexYAmmNArr95t8syrU/E+cuIMORsByxlTLc43yXUbXSX+M8ZSv9OpyPVfrGeN2A8lMKi6qFmxInRm0eiYIvBFozxoM44BW9rJGRXcGCNo9/hOCMhpV/2wsE6CFCIljEQyAVZBX5ir5E7juHHCwPxkAv82ui8ckbu+c4T0yvCY4OYkdrgdRz+kSTKePOKjKUlW9QB2vVZOrZJYbsFAThe7SJ6iSGMOSxXtubNkjp82JzB5sjexDew2bDdwru2Q9fHe4Y54te6yJX3JYOGCArR6e7nN/9VSjQ0NzVaUUs2NThWQlhX";
try {
    $decryptedContent = comicApiDecryptV1($content);
    echo "Decrypted content: " . $decryptedContent;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>