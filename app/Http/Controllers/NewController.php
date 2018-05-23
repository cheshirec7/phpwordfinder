<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NewController extends Controller {
	function index() {
		return view( 'app' );
	}

	function startswith( Request $request ) {
		$words = DB::table( 'wwfdict' )
		           ->select( 'id', 'word', 'keep' )
		           ->whereRaw( 'word like "' . $request->startsWith . '%"' )
		           ->orderByRaw( 'CHAR_LENGTH(word)' )
		           ->orderBy( 'word' )
		           ->get();

		return view( 'content', [ 'words' => $words ] );
	}

	function updateword( Request $request ) {
		DB::table( 'wwfdict' )
		  ->where( 'word', $request->word )
		  ->update( [ 'keep' => $request->k ] );
	}

	public function loaddb() {

		DB::table( 'wwfdict' )->truncate();
//		$thecount = 0;
		for ( $x = 2; $x <= 15; $x ++ ) {
			$wordsarr = explode( '*', file_get_contents( 'wordlists/wl_' . $x . '.txt', false ) );
//			$thecount += count($wordsarr);
			foreach ( $wordsarr as $word ) {
				DB::table( 'wwfdict' )->insert(
					[ 'word' => $word ]
				);
			}
		}
		return 'complete';
//		return 'complete: count='.$thecount;
	}
}
