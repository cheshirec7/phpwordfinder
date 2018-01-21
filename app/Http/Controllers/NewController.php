<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NewController extends Controller
{
    function index()
    {
        return view('app');
    }

    function startswith(Request $request)
    {
        $words = DB::table('dict')
            ->select('id', 'word', 'keep')
            ->whereRaw('word like "' . $request->startsWith . '%"')
            ->orderByRaw('CHAR_LENGTH(word)')
            ->orderBy('word')
            ->get();

        return view('content', ['words' => $words]);
    }

    function updateword(Request $request)
    {
        DB::table('dict')
            ->where('word', $request->word)
            ->update(['keep' => $request->k]);
    }

    public function loaddb()
    {

//        UPDATE dict
// SET word=REPLACE(word,'\n','')

        foreach (file('wwf.txt') as $line) {

            $line = trim($line);
//            if (strlen($line))
                DB::table('dict')->insert(
                    ['word' => $line]
                );
        }
        return view('loaddb');
    }
}
