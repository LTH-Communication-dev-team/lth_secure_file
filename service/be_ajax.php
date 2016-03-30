<?php
class lth_secure_file_ajax {
    
    public function ajaxControl() {
        $file = t3lib_div::_GP('file');

	$content = $this->addRemoveHtaccess($file);
        
        echo json_encode($content);
    }
    
    public function addRemoveHtaccess($file)
    {
            $content = '';
            $fileName = $file . ".htaccess";
            if(is_file($fileName)) {
                    unlink($fileName);
                    $content = "Directory <b>unlocked</b>: $file";
            } else {
                    $fh = fopen($fileName, 'w') or die("can't open file");
                    $stringData = 'RedirectMatch "(.*)$" /index.php?eID=lth_secure_file\&action=fileOutput\&sid=' . mt_rand() . '\&file=$1';
                    fwrite($fh, $stringData) or die("can't write file");
                    fclose($fh);
                    $content = "Directory <b>locked</b>: $file";	
            }

            return $content;
            //RedirectMatch "(.*)$" /typo3conf/ext/lth_secure_file/res/push.php?file=$1
    }
}