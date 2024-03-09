<?php

/**
 * majie <jie1.ma@linktone.com>
 */
error_reporting(0);
Class semdMail{
    Function __construct(){
        $this->set();
        $this->auth = 1;


    }

    Function set($server=BLK_SysMail_Smtp,$user="",$password=BLK_SysMail_Pass,$port=BLK_SysMailPort,$type=1,$mailusername=0){
        $user=($user=="")?SubStr(BLK_SysMail,0,Stripos(BLK_SysMail,"@")):$user;

        $this->type = $type;
        $this->server = $server;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->mailusername = $mailusername;
    }

    Function Send($email_to, $email_subject, $email_message, $email_from = ''){
        $email_subject = '=?utf-8?B?'.base64_encode(str_replace("\r", '', $email_subject)).'?=';
        $email_message = str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $email_message)))));
        $email_from = $email_from == '' ? '=?utf-8?B?'.base64_encode(BLK_SysName)."?= <".BLK_SysMail.">" : (preg_match('/^(.+?) \<(.+?)\>$/',$email_from, $from) ? '=?utf-8?B?'.base64_encode($from[1])."?= <$from[2]>" : $email_from);
        $emails = explode(',', $email_to);
        Foreach($emails as $touser){
            $tousers[] = preg_match('/^(.+?) \<(.+?)\>$/',$touser, $to) ? ($this->mailusername ? '=?utf-8?B?'.base64_encode($to[1])."?= <$to[2]>" : $to[2]) : $touser;
        }
        $email_to = implode(',', $tousers);
        $headers = "MIME-Version: 1.0\r\nFrom: {$email_from}\r\nX-Priority: 3\r\nX-Mailer: BlackHand \r\nDate: ".date("r")."\r\nContent-type: text/html; charset=UTF-8\r\n";
        IF($this->type == 1){

            Return $this->smtp($email_to, $email_subject, $email_message, $email_from, $headers);
        }ElseIF($this->type == 2){
            Return @mail($email_to, $email_subject, $email_message, $headers);
        }Else{
            ini_set('SMTP', $this->server);
            ini_set('smtp_port', $this->port);
            ini_set('sendmail_from', $email_from);
            Return @mail($email_to, $email_subject, $email_message, $headers);
        }
    }

    Function smtp($email_to, $email_subject, $email_message, $email_from = '', $headers = ''){
        IF(!$fp = @fsockopen($this->server, $this->port, $errno, $errstr, 10)){
            echo '<pre>';
            var_dump('SMTP', "($this->server:$this->port) CONNECT - Unable to connect to the SMTP server");
            echo '</pre>';
            exit;
            $this->errorlog('SMTP', "($this->server:$this->port) CONNECT - Unable to connect to the SMTP server", 0);

            return false;
        }
        stream_set_blocking($fp, true);
        $lastmessage = fgets($fp, 512);

        IF(substr($lastmessage, 0, 3) != '220'){
            $this->errorlog('SMTP', "$this->server:$this->port CONNECT - $lastmessage", 0);
            return false;
        }

        fputs($fp, ($this->auth ? 'EHLO' : 'HELO')." Phpcms\r\n");
        $lastmessage = fgets($fp, 512);

        IF(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250){
            $this->errorlog('SMTP', "($this->server:$this->port) HELO/EHLO - $lastmessage", 0);
            return false;
        }

        While(1){
            IF(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)){
                break;
            }
            $lastmessage = fgets($fp, 512);
        }
        fputs($fp, "AUTH LOGIN\r\n");
        $lastmessage = fgets($fp, 512);

        IF(substr($lastmessage, 0, 3) != 334){
            $this->errorlog('SMTP', "($this->server:$this->port) AUTH LOGIN - $lastmessage", 0);
            return false;
        }
        fputs($fp, base64_encode($this->user)."\r\n");
        $lastmessage = fgets($fp, 512);
        IF(substr($lastmessage, 0, 3) != 334){
            $this->errorlog('SMTP', "($this->server:$this->port) USERNAME - $lastmessage", 0);
            return false;
        }

        fputs($fp, base64_encode($this->password)."\r\n");
        $lastmessage = fgets($fp, 512);

        IF(substr($lastmessage, 0, 3) != 235){
            $this->errorlog('SMTP', "($this->server:$this->port) PASSWORD - $lastmessage", 0);
            return false;
        }
        fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
        $lastmessage = fgets($fp, 512);
        IF(substr($lastmessage, 0, 3) != 250){
            fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
            $lastmessage = fgets($fp, 512);
            IF(substr($lastmessage, 0, 3) != 250){
                $this->errorlog('SMTP', "($this->server:$this->port) MAIL FROM - $lastmessage", 0);
                return false;
            }
        }
        $email_tos = array();
        $emails = explode(',', $email_to);

        Foreach($emails as $touser){
            $touser = trim($touser);
            IF($touser){
                fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
                $lastmessage = fgets($fp, 512);
                IF(substr($lastmessage, 0, 3) != 250){
                    fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $touser).">\r\n");
                    $lastmessage = fgets($fp, 512);
                    $this->errorlog('SMTP', "($this->server:$this->port) RCPT TO - $lastmessage", 0);
                    return false;
                }
            }
        }
        fputs($fp, "DATA\r\n");
        $lastmessage = fgets($fp, 512);
        IF(substr($lastmessage, 0, 3) != 354){
            $this->errorlog('SMTP', "($this->server:$this->port) DATA - $lastmessage", 0);
        }
        $headers .= 'Message-ID: <'.gmdate('YmdHs').'.'.substr(md5($email_message.microtime()), 0, 6).rand(100000, 999999).'@'.$_SERVER['HTTP_HOST'].">\r\n";

        fputs($fp, "Date: ".gmdate('r')."\r\n");
        fputs($fp, "To: ".$email_to."\r\n");
        fputs($fp, "Subject: ".$email_subject."\r\n");
        fputs($fp, $headers."\r\n");
        fputs($fp, "\r\n\r\n");
        fputs($fp, "$email_message\r\n.\r\n");
        $lastmessage = fgets($fp, 512);
        fputs($fp, "QUIT\r\n");
        return true;
    }

    Function errorlog($type, $message, $is){
        $this->error[] = array($type, $message, $is);
    }

    Function Mailmess($Mess=""){
        Return "<style>*{font-family:Verdana;font-size:13px;}</style>
        <table cellpadding='5' cellspacing='1' border='0' width='750' style='background-color:#fff;border:1px solid #3b5998;padding:15px;'>
        <tr><td style='background:#3b5998;color:#fff;font-weight:bold;font-size:14px;'>".BLK_SysCnname." - ".BLK_SysName."</td></tr>
        <tr><td>{$Mess}</td></tr>
        <tr><td style='border-top:1px dashed gray;'><span style='color:gray;'>該郵件由軟體<a href='http://www.7di.net' target='_blank'>".BLK_SysName."</a>自動發送,如需技術支持請發信至See7di@Gmail.Com或聯系QQ:9256114.</span></td></tr>
        </table>";
    }


	  /**
     * 发送带附件邮件
     * @param unknown $email_to
     * @param unknown $email_subject
     * @param unknown $email_message
     * @param unknown $attaches
     * @param string $email_from
     * @return boolean
     */
    Function smtpAttach($email_to, $email_subject, $email_message,$attaches, $email_from = EMAIL_USER){

        $this->set(EMAIL_SMTP, EMAIL_USER, EMAIL_PWD, EMAIL_PORT);
        $eol = PHP_EOL;
        $separator ="--=". md5(time());
        //$bodySep= "--=".md5(time()+100);
        $bodySep= $separator;
        $email_subject= mb_encode_mimeheader (mb_convert_encoding($email_subject,"UTF-8","AUTO"));
        $email_from_name=mb_encode_mimeheader (mb_convert_encoding('父母邦',"UTF-8","AUTO"));
        // main header
        $headers  = "From: ".$email_from_name.'<'.$email_from.'>'.$eol;
        $headers .= "MIME-Version: 1.0".$eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";

        // no more headers after this, we start the body! //
        //$body[]= "--".$separator.$eol;
        //$body[]= "Content-Transfer-Encoding: 7bit".$eol.$eol;
        //$body[]= "This is a MIME encoded message.".$eol;

        // message
        $body[]= "--".$bodySep.$eol;
        $body[]= "Content-Type: text/html; charset=\"UTF-8\"".$eol;
        $body[]= "Content-Transfer-Encoding: 8bit".$eol.$eol;

        $body[]= mb_convert_encoding($email_message,"UTF-8","AUTO").$eol;
        //$body[]= "Content-Transfer-Encoding: base64".$eol.$eol;
        //$body[]= base64_encode($email_message).$eol;

        if($attaches && is_array($attaches)){
            foreach($attaches as $filename){
                if(is_file($filename)){
                    $attachment=chunk_split(base64_encode(file_get_contents($filename)));
                    $attachmentName=pathinfo($filename,PATHINFO_BASENAME);
                    //attachment
                    $body[]= "--".$separator.$eol;
                    $body[]= "Content-Type: application/octet-stream; name=\"".$attachmentName."\"".$eol;
                    $body[]= "Content-Transfer-Encoding: base64".$eol;
                    $body[]= "Content-Disposition: attachment".$eol.$eol;
                    $body[]= $attachment.$eol;

                }
            }
            $body[]= "--".$separator."--".$eol;
        }


        $body=implode("", $body);

      //print_r(array($email_to, $email_subject, $email_from , $headers));
      // send message
      $res= $this->smtp($email_to, $email_subject, $body, $email_from , $headers);
      //print_r($this->error);
      return $res;
    }

}

