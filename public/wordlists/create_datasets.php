<?php
set_time_limit(0);

const DB_TYPE = 'mysql';
const DB_HOST = 'localhost';
const DB_NAME = 'words';
const DB_USER = 'root';
const DB_PASS = 'neelix1';

mysql_connect(DB_HOST,DB_USER,DB_PASS);
mysql_select_db(DB_NAME);
				
function length_then_alpha_sort($a,$b){
	$blen = strlen($b);
	$alen = strlen($a);
	if ($blen != $alen)
		return ($alen-$blen);
	else
		return strcmp($a,$b);
}

function create_sorted_combined(){
	$file = file_get_contents('word_list_unsorted.txt');
	$words_orig = explode('*',$file);
	usort($words_orig,'length_then_alpha_sort');
	$fh1 = fopen("word_list_sorted.txt", "w");
	foreach ($words_orig as $idx=>$word) {
		if (strlen($word) < 25)
			fwrite($fh1,$word.'*');
	}
	fclose($fh1);
}	

function create_sorted_ana(){
	$file = file_get_contents('word_list_sorted.txt');
	$words_orig = explode('*',$file);

	$words_ana = [];
	foreach ($words_orig as $idx=>$word) {
		$ana = str_split($word);
		sort($ana);
		$ana=implode($ana);
		if ($ana != '')
			$words_ana[] = $ana;
	}
	$words_ana = array_unique($words_ana);
	usort($words_ana,'length_then_alpha_sort');
	$fh1 = fopen("ana_sorted.txt", "w");
	foreach ($words_ana as $idx=>$word) {
		fwrite($fh1,$word.'*');
	}
	fclose($fh1);
}
	
function create_wordlists($len){
	$arr_keys = array();
	$arr_data = array();
	
	$file = file_get_contents('word_list_sorted.txt');
	$words_orig = explode('*',$file);
	unset($file);
	
	foreach ($words_orig as $idx=>$word) {
		if (strlen($word) == $len) {
			$arr_data[] = $word;
		}
	}
	unset($words_orig);
	
	$file = file_get_contents('ana_sorted.txt');
	$words_ana = explode('*',$file);
	unset($file);
	
	foreach ($words_ana as $idx=>$ana) {
		if (strlen($ana) == $len) {
			$arr_keys[] = $ana;
		}
	}
	unset($words_ana);
	$arr_keys_copy = $arr_keys;
	
	foreach ($arr_data as $idx=>$word) {
		$orig_word = $word;
		$ana = str_split($word);
		sort($ana);
		$ana=implode($ana);
		$pos = array_search($ana,$arr_keys);
		$arr_keys_copy[$pos] .= ' '.$orig_word;
	}
	
	$fh1 = fopen("wordlist_keys_".$len.".txt", "w");
	foreach ($arr_keys as $idx=>$key) {
		fwrite($fh1,$key.'*');
	}
	fclose($fh1);
	
	$sql = 'CREATE TABLE words'.$len.' (id char('.$len.') NOT NULL,wordlist varchar(100) NOT NULL,PRIMARY KEY (id)) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
	mysql_query($sql);

	$fh1 = fopen("wordlist_".$len.".txt", "w");
	$fh2 = fopen("wordlist_data_".$len.".txt", "w");
	$fh3 = fopen("wordlist_apc_".$len.".php", "w");
	
	fwrite($fh3,'<?php'.PHP_EOL);
	fwrite($fh3,'$cache_type = "user";'.PHP_EOL);
	fwrite($fh3,'apc_clear_cache($cache_type);'.PHP_EOL);
		
	foreach ($arr_keys_copy as $idx=>$keydata) {
		fwrite($fh1,$keydata.'*');
		$key = substr($keydata, 0, $len);
		$data = substr($keydata, $len+1);
		fwrite($fh2,$data.'*');
		
		$sql = 'INSERT INTO words'.$len.'(id,wordlist) VALUES("'.$key.'","'.$data.'")';
		mysql_query($sql);
		
		fwrite($fh3,'apc_store("'.$key.'","'.$data.'");'.PHP_EOL);	
	}
	fclose($fh1);
	fclose($fh2);
	
	fwrite($fh3,'apc_bin_dumpfile(array(),null,"wordlist_dump_'.$len.'.apc");');
	fclose($fh3);
}

//create_sorted_combined();
//create_sorted_ana();
for ($i=2;$i < 25;$i++) {
	create_wordlists($i);
}