<?
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/nail/simple_html_dom.php"); //подключаем библиотеку

    // Максиальное время выполнения 
    ini_set('max_execution_time', '10000');
    $filename = '/home/v/vemnail/nailshome.ru/exch-bonus/bonus (HTML4).html';

if (file_exists($filename)) {
 
    // Отбираем номера телефонов с ID
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
        $BXdataUser['IDUser'] = $arUser['ID'];
        $BXdataUserAll[] = $BXdataUser;
        
        
    };

    $htmlFile = file_get_html( $filename );
    $seach = $htmlFile->find('TR');
    
    // Выбираеи номера телефонов и кол-во баллов     
    foreach($seach as $tag) {
        $data['phone'] = $tag->find('.R2C1', 0)->plaintext;
        $data['col'] = $tag->find('.R2C2', 0)->plaintext;
        if (($data['phone'] != " ") && $data['phone'] != NULL){
            $dataset[] = $data;
        };
    };
    
    //Перемещаем кол-во из одного массива в другой для отбора
    foreach ($BXdataUserAll as $key=>$val){
        $val['numberUser'] = str_replace(array('+', ' ', '(' , ')', '-'), '', $val['numberUser']);
        $val['numberUser'] = preg_replace('~\d~', '', $val['numberUser'], 1);
            foreach($dataset as $k=>$v){
                $v['phone'] = str_replace(array('+', ' ', '(' , ')', '-'), '', $v['phone']);
                $v['phone'] = preg_replace('~\d~', '', $v['phone'], 1);
                if( $val['numberUser'] === $v['phone']){
                    $BXdataUserAll[$key]['col'] = $v['col'];
                };
            };
    };
    
    //Узнам id-бонусного счета и помещаем в общий массив
    foreach ($BXdataUserAll as $key=>$val){
        if ($ar = CSaleUserAccount::GetByUserID($val['IDUser'], 'RUB')){
                $BXdataUserAll[$key]['IDBonus'] = $ar['ID'];
        };
    };
    
    //Перебираем полученный массив и записываем бонусы или создаем бонусную программу, если ее не было
    foreach ($BXdataUserAll as $val){
        $result = false;
        if($val['col'] != 0 || !(empty($val['col']) && ($val['IDBonus'] != 0 || $val['IDBonus'] != " "))){
            $arFields = array("CURRENT_BUDGET" => $val['col']);
           
            $result = CSaleUserAccount::Update($val['IDBonus'], $arFields);
        };
        
        if (($val['IDBonus'] == NULL || $val['IDBonus'] == 0) && $val['col'] > 0){
            $arFields = array("USER_ID" => $val['IDUser'], 
                              "CURRENCY" => "RUB", 
                              "CURRENT_BUDGET" => $val['col'],
                              "NOTES" => "Обновление бонусов из программы 1С",
                              "LOCKED" => false
                              );
            $result = CSaleUserAccount::Add($arFields); 
        }
    };
    
} else {
   echo "Файл filename не существует";
};

    $htmlFile->clear();
    unset($htmlFile);
//    unlink('/home/v/vemnail/nailshome.ru/exch-bonus/bonus (HTML4).html');
    