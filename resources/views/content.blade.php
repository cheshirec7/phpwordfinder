@extends('app')
@section('content')
    @php
        $wlen=1;
    @endphp
    @foreach ($words as $word)
        @php
            if ($wlen != strlen($word->word)) {
                if ($wlen != 1) {
                    echo '</div>';
                }
            echo '<p>'.strlen($word->word).' Letter Words</p>';
            echo '<div class="wordcontainer">';
            $wlen = strlen($word->word);
        }
        @endphp

        <div data-k="{!! $word->keep !!}">{!! $word->word !!}</div>

    @endforeach
    </div>
    <br/><br/>
@endsection