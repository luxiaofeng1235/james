<?php
class ChapterAes{


    private  $skey = 'Pxga!h*e4@T8xfOm'; //解密的key
    private  $iv = 'E&z!EHGLd$fli*8R'; //解密的iv
    /**
    * 解密函数
    *
    * @param string $src
    * @return string|null
    */
   public function pswDecryptString($src) {
        if(!$src){
            return false;
        }
        $decodedData = base64_decode($src);
        if ($decodedData === false) {
            return null;
        }
        $decryptedData = $this->aes128Decrypt($decodedData, $this->skey, $this->iv);
            
        return $decryptedData !== false ? $decryptedData : null;
    }

    /**
     * AES-128-CBC 解密
     *
     * @param string $crypted
     * @param string $key
     * @param string $iv
     * @return string|false
     */
   public function aes128Decrypt($crypted, $key, $iv) {
        // 检查密钥和 IV 长度
        if (strlen($key) !== 16 || strlen($iv) !== 16) {
            return false;
        }
        // 使用 OpenSSL 解密
        $decrypted = openssl_decrypt($crypted, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
            
        if ($decrypted === false) {
            return false;
        }
        return $decrypted;

        // 去掉 PKCS#7 填充
        return $this->pkcs5Unpadding($decrypted);
    }

    /**
     * PKCS#5 去填充
     *
     * @param string $data
     * @return string
     */
  public  function pkcs5Unpadding($data) {
        $length = strlen($data);
        $unpadding = ord($data[$length - 1]);
        return substr($data, 0, $length - $unpadding);
    }
}
?>