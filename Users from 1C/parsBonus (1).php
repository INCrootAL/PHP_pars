<?php

    
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/nail/simple_html_dom.php"); //подключаем библиотеку

// Максиальное время выполнения 
ini_set('max_execution_time', '10000');
$filename = '/home/v/vemnail/nailshome.ru/exch-bonus/сontrsbonss (HTML4).html';

if (file_exists($filename)) {

    $htmlFile = file_get_html( $filename );
    $seach = $htmlFile->find('TR');
    foreach($seach as $tag) {
        
        $data['FIO'] = $tag->find('.R2C0', 0)->plaintext;
        $arrayNew = explode(" " , $data['FIO'], 3);
        $data['surname'] = $arrayNew[0];
        $data['lastname'] = $arrayNew[1];
        $data['phone'] = $tag->find('.R2C1', 0)->plaintext;
        $data['phone'] = str_replace(array('+', ' ', '(' , ')', '-'), '', $data['phone']);
        $data['data'] = $tag->find('.R2C1', 1)->plaintext;
        $data['mail'] = $tag->find('.R2C1', 2)->plaintext;

        if ((($data['FIO'] != " ") && $data['FIO'] != NULL) && ($data['surname'] != "Держатель")){
            $dataset[] = $data;
        };
    };
    
    $BXdata = CUser::GetList(($by="ID"), ($order="ASC"),
    array(
     'ACTIVE' => 'Y', // Выбрали всех активных
    ));
    
    while($arUser = $BXdata->Fetch()) {
         
        if(!(empty($arUser['WORK_PHONE']))){ 
            $BXdataUser['numberUser'] = $arUser['WORK_PHONE'];
        } else {
            $BXdataUser['numberUser'] = $arUser['PERSONAL_PHONE'];
        };
        $BXdataUser['name'] = $arUser['NAME'];
        $BXdataUser['LAST_NAME'] = $arUser['LAST_NAME'];
       $BXdataUser['IDUser'] = $arUser;
        $BXdataUserAll[] = $BXdataUser;
        
    };
    /*
    echo "<pre>";
    print_r($BXdataUserAll);
    echo "<pre>";
   */ 
   function generateAll($length = 6){
        $chars = 'abdefhiknrstyzabdefhiknrstyz1234567890';
        $numChars = strlen($chars);
        $string = '';
            for ($i = 0; $i < $length; $i++) {
                $string .= substr($chars, rand(1, $numChars) - 1, 1);
            };
    return $string;
    };
    
    function generatePass($length = 6){
        $chars = 'abdefhiknrstyzABDEFHIKNRSTYZ1234567890';
        $numChars = strlen($chars);
        $string = '';
            for ($i = 0; $i < $length; $i++) {
                $string .= substr($chars, rand(1, $numChars) - 1, 1);
            };
    return $string;
    };
   
    foreach ($dataset as $key=>$v){
        $v['phone'] = str_replace(array('+', ' ', '(' , ')', '-'), '', $v['phone']);
        $v['phone'] = preg_replace('~\d~', '', $v['phone'], 1);
            foreach($BXdataUserAll as $k=>$val){
                $val['numberUser'] = str_replace(array('+', ' ', '(' , ')', '-'), '', $val['numberUser']);
                $val['numberUser'] = preg_replace('~\d~', '', $val['numberUser'], 1);
                    if(($val['numberUser'] != $v['phone']) && (($val['name'] != v['lastname'])  || ($val['LAST_NAME'] != v['surname']))){
                        
                        $BXNewLogin = generateAll();
                        $domen = "@nailshome.ru";
                        
                        if($v['mail'] != " " && $v['mail'] != NULL ){
                            $Email = $v['mail'];
                        } else {
                            $Email = $BXNewLogin . $domen ;
                        };
                        
                        $BXNewPass = generatePass();
                       
                        $user = new CUser;
                        $arFields = Array(
                                    	"NAME"              => $v['lastname'],
                                    	"LAST_NAME"         => $v['surname'],
                                    	"EMAIL"             => $Email,
                                    	"LOGIN"             => $BXNewLogin,
                                    	"LID"               => "s1",
                                    	"WORK_PHONE"        =>  $v['phone'],
                                    	"PERSONAL_PHONE"    =>  $v['phone'],
                                    	"PERSONAL_MOBILE"   =>  $v['phone'],
                                    	"PHONE_NUMBER"      =>  $v['phone'],
                                    	"PERSONAL_BIRTHDAY" =>  $v['data'],
                                    	"ACTIVE"            => "Y",
                                    	"GROUP_ID"          => array(3,7),
                                    	"PASSWORD"          => $BXNewPass,
                                    	"CONFIRM_PASSWORD"  => $BXNewPass,
                        );
                        $ID = $user->Add($arFields);
                    };
            };
    };
    
    print_r("Выпонено!");
    
} else {
    
   echo "Файл $filename не существует";

    
};

$htmlFile->clear();
unset($htmlFile);
unlink('/home/v/vemnail/nailshome.ru/exch-bonus/сontrsbonss (HTML4).html');
