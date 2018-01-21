@extends('app')
@push('after-styles')
    <style>
        #resultsArea p {
            font-weight: bold;
            margin: 10px 0 5px;
            text-align: center;
            border-bottom: 1px dashed #999;
        }

        .wordcontainer {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .wordcontainer div {
            display: inline;
            padding: 10px;
            color: #3366ff;
            font-family: monospace;
            font-size: 22px;
            line-height: 22px;
            cursor: pointer;
        }

        .wordcontainer div[data-k="0"] {
            color: #ccc;
        }

    </style>
@endpush
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
@push('after-scripts')
    <script>
        $(document).ready(function () {
            let $div = $('div', '.wordcontainer').click(function () {
                let $this = $(this),
                    k = $this.attr('data-k'),
                    word = $this.html();

                $.ajax({
                    type: 'POST',
                    url: 'updateword',
                    data: {
                      word: word,
                      k: 1-k
                    },
                    success: function (data) {
                        $this.attr('data-k', 1-k);
                        // console.log(data);
                    },
                    error: function (data) {
                        console.log('Error:', data);
                    }
                });

            });
        });
    </script>
@endpush
