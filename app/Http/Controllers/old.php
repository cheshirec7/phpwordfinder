<?php

const ASCII_a = 97;
const ASCII_z = 122;

const NUM_WORDLIST_FILES = 24;

const SEARCH_MODE_PHP_FILES = 0;
const SEARCH_MODE_PHP_PDO = 1;
const SEARCH_MODE_PHP_MYSQLI = 2;
const SEARCH_MODE_PHP_NATIVE = 3;
const SEARCH_MODE_PHP_APC = 4;
const SEARCH_MODE_GO_FILES = 5;
const SEARCH_MODE_PYTHON_FILES = 6;
const SEARCH_MODE_PYTHON_NDB = 7;
const SEARCH_MODE_NODEJS_FILES = 8;
const SEARCH_MODE_NODEJS_MYSQL = 9;
const SEARCH_MODE_NODEJS_MEMCACHIER = 10;
const SEARCH_MODE_NODEJS_MONGO = 11;
const SEARCH_MODE_MAX_ID = 11;

const RETURN_TYPE_HTML = 'html';
const RETURN_TYPE_JSON = 'json';

class Wordfinder
{
    public $tray_validated = '';
//  public $lettersonly = '';
    public $search_mode = SEARCH_MODE_PHP_FILES;
    public $return_type = RETURN_TYPE_HTML;
    public $inputerrmsg = '';

    private $time_start;
    private $total_found = 0;
    private $total_compares = 0;

    private $letter_counts_arr = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
    private $wild_count = 0;
    private $cache_count = 0;

    private $twentysix_zeros_arr = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
    # most_to_least_common = (E, A, R, I, O, T, N, S, L, C, U, D, P, M, H, G, B, F, Y, W, K, V, X, Z, J, Q)
    private $most_to_least_common = array(4, 0, 17, 8, 14, 19, 13, 18, 11, 2, 20, 3, 15, 12, 7, 6, 1, 5, 24, 22, 10, 21, 23, 25, 9, 16);
    private $pubpath = '';

    public function __construct()
    {
//        $this->pubpath = env('PUBLIC_PATH', base_path('public'));
    }

    /////
    private function validateInput($tray)
    {
        for ($i = 0,$j = strlen($tray); $i < $j; $i++) {
            $charcode = ord($tray[$i]);
            if (($charcode >= ASCII_a) && ($charcode <= ASCII_z)) {
                $this->letter_counts_arr[$charcode-ASCII_a] += 1;
                $this->tray_validated .= $tray[$i];
            }
        }

        $this->lettersonly = $this->tray_validated;
        for ($i = 0, $j = $this->wild_count; $i < $j; $i++)
            $this->tray_validated .= '?';

        if (strlen($this->tray_validated) < 2) {
            $this->inputerrmsg = 'Format: phpwordfinder.erictotten.net/find?tray=test&rt=[html,json]&wc=[0+]';
            return false;
        }

        return true;
    }


    private function getFooter() {
        $total_time_secs = ceil((microtime(true)-$this->time_start)*1000)/1000;

        $res = '';
        if ($this->total_found == 0)
            $res .= '<p class="noresults">No words found.</p>';

        $res .= '<p id="results_footer">';

        $res .= 'Compares: '.$this->total_compares.'. Results: '.$this->total_found.' in '.$total_time_secs.'s.';
        $res .= ' Memcached: '.$this->cache_count;
        switch ($this->search_mode) {
            case SEARCH_MODE_PHP_FILES: $res .= '<br />PHP '.phpversion();break;
            case SEARCH_MODE_PHP_PDO: $res .= '<br />PHP 5.5, PDO SQL driver';break;
            //      case SEARCH_MODE_PHP_APC: $res .= '<br />PHP 5.5, searching Alternative PHP Cache';break;
        }
        $res .= '</p>';
        return $res;
    }

    /////
    private function nullifyUnmatchedWords($word_len,&$words_arr) {
        $num_found = count($words_arr);
        foreach ($words_arr as $i => $word) {
            $ana_key_letter_counts_arr = $this->twentysix_zeros_arr;
            for ($j = 0; $j < $word_len; $j++)
                $ana_key_letter_counts_arr[ord($word[$j])-ASCII_a]++;
            for ($j = 0; $j < 26; $j++) {
                $this->total_compares++;
                $idx = $this->most_to_least_common[$j];
                if ($this->letter_counts_arr[$idx] - $ana_key_letter_counts_arr[$idx] < 0) {
                    $words_arr[$i] = NULL;
                    $num_found--;
                    break;
                }
            }
        }
        return $num_found;
    }

    /////
    private function nullifyUnmatchedWordsWild($word_len,&$words_arr) {
        $num_found = count($words_arr);
        foreach ($words_arr as $i => $word) {
            $ana_key_letter_counts_arr = $this->twentysix_zeros_arr;
            for ($j = 0; $j < $word_len; $j++)
                $ana_key_letter_counts_arr[ord($word[$j])-ASCII_a]++;
            $wild_avail = $this->wild_count;
            for ($j = 0; $j < 26; $j++) {
                $this->total_compares++;
                $idx = $this->most_to_least_common[$j];
                $diff = $this->letter_counts_arr[$idx] - $ana_key_letter_counts_arr[$idx];
                if ($diff < 0) {
                    $wild_avail += $diff;
                    if ($wild_avail < 0) {
                        $words_arr[$i] = NULL;
                        $num_found--;
                        break;
                    }
                }
            }
        }
        return $num_found;
    }
    /////
    private function getWordsArr($word_len) {

//        $key = 'wl_'.$word_len;
//        if (Cache::has($key)) {
//            $this->cache_count += 1;
//            return Cache::get($key);
//        }

//        $filename = $this->pubpath . "/wordlists/wl_" . $word_len . '.txt';
        $filename = "wordlists/wl_" . $word_len . '.txt';
        $wordlist = explode("*", file_get_contents($filename));
//        Cache::forever($key, $wordlist);
        return $wordlist;
    }

    private function getHTML($word_len,$words_arr) {
        $res = '<p>'.$word_len.'-Letter Words</p><div class="wordcontainer">';
        foreach($words_arr as $word) {
            if ($word !== NULL)
                $res .= '<div>'.$word.'</div>';
        }
        $res .= '</div>';
        return $res;
    }

    public function index(Request $request)
    {
//        $inputs = $request->only('tray', 'sm', 'rt', 'wc');
//
//        if ($request->has('rt')) {
//            if ($inputs['rt'] == RETURN_TYPE_JSON)
//                $this->return_type = RETURN_TYPE_JSON;
//        }
//
//        if ($request->has('wc')) {
//            $this->wild_count = intval($inputs['wc']);
//            if ($this->wild_count < 0)
//                $this->wild_count = 0;
//            else if ($this->wild_count > NUM_WORDLIST_FILES)
//                $this->wild_count = NUM_WORDLIST_FILES;
//        }
//
//        //    if (!$this->validateInput($tray))
//        if (!$this->validateInput($inputs['tray'])) {
//            if ($this->return_type == RETURN_TYPE_JSON)
//                return response()->json(array('error' => true, 'msg' => $this->inputerrmsg));
//            else
//                return '<p class="text-center">'.$this->inputerrmsg.'</p>';
//        }


        $this->time_start = microtime(true);


        $json_arr = array();
        $res = '';

        $ana_key_len = strlen($this->tray_validated);
        if ($ana_key_len > NUM_WORDLIST_FILES)
            $ana_key_len = NUM_WORDLIST_FILES;

        for ($word_len = $ana_key_len; $word_len > 1; $word_len--) {

            $words_arr = $this->getWordsArr($word_len);

            if ($this->wild_count == 0)
                $num_found = $this->nullifyUnmatchedWords($word_len, $words_arr);
            else if ($word_len > $this->wild_count)
                $num_found = $this->nullifyUnmatchedWordsWild($word_len, $words_arr);
            else
                $num_found = count($words_arr);

            if ($this->return_type == RETURN_TYPE_JSON) {
                if ($num_found == count($words_arr))
                    $json_arr[] = $words_arr;
                else if ($num_found > 0) {
                    $temp = array();
                    foreach ($words_arr as $word) {
                        if ($word)
                            $temp[] = $word;
                    }
                    $json_arr[] = $temp;
                }
            } else if ($num_found > 0) {
                $res .= $this->getHTML($word_len, $words_arr);
                $this->total_found += $num_found;
            }
            unset($words_arr);
        }

        if ($this->return_type == RETURN_TYPE_JSON)
            return $json_arr;
//      return response()->json(array('error' => false, 'words' =>$json_arr));

        $result = '<h5 id="resultsFor">Results for <span>';
        $result .= $this->tray_validated;
        $result .= '</span></h5>';

        return $result.$res.$this->getFooter();

//    return (new Response($result.$res.$this->getFooter(),200));

    }
}

$wf = new Wordfinder;
var_dump($message);