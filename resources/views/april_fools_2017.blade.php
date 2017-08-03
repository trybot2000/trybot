<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>TryBot2000</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="{{ URL::asset('js/april_1.js') }}"></script>
    <script src="{{ URL::asset('js/f.js') }}"></script>

    <link rel="stylesheet" href="{{ URL::asset('css/app.css') }}">
    <!--

███╗   ██╗ ██████╗ ████████╗██╗  ██╗██╗███╗   ██╗ ██████╗
████╗  ██║██╔═══██╗╚══██╔══╝██║  ██║██║████╗  ██║██╔════╝
██╔██╗ ██║██║   ██║   ██║   ███████║██║██╔██╗ ██║██║  ███╗
██║╚██╗██║██║   ██║   ██║   ██╔══██║██║██║╚██╗██║██║   ██║
██║ ╚████║╚██████╔╝   ██║   ██║  ██║██║██║ ╚████║╚██████╔╝
╚═╝  ╚═══╝ ╚═════╝    ╚═╝   ╚═╝  ╚═╝╚═╝╚═╝  ╚═══╝ ╚═════╝

████████╗ ██████╗     ███████╗███████╗███████╗
╚══██╔══╝██╔═══██╗    ██╔════╝██╔════╝██╔════╝
   ██║   ██║   ██║    ███████╗█████╗  █████╗
   ██║   ██║   ██║    ╚════██║██╔══╝  ██╔══╝
   ██║   ╚██████╔╝    ███████║███████╗███████╗
   ╚═╝    ╚═════╝     ╚══════╝╚══════╝╚══════╝

██╗███╗   ██╗    ██╗  ██╗███████╗██████╗ ███████╗       ██╗
██║████╗  ██║    ██║  ██║██╔════╝██╔══██╗██╔════╝    ██╗╚██╗
██║██╔██╗ ██║    ███████║█████╗  ██████╔╝█████╗      ╚═╝ ██║
██║██║╚██╗██║    ██╔══██║██╔══╝  ██╔══██╗██╔══╝      ██╗ ██║
██║██║ ╚████║    ██║  ██║███████╗██║  ██║███████╗    ╚═╝██╔╝
╚═╝╚═╝  ╚═══╝    ╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝╚══════╝       ╚═╝


-->


<!-- Styles -->
<style>
    html, body {
        background-color: #eee;
        color: #636b6f;
        font-family: sans-serif;
        font-weight: 100;
        height: 100vh;
        margin: 0;
        overflow: hidden;
    }

    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .content {
        text-align: center;
        margin-top: 13%;
    }

    .title {
        font-size: 84px;
    }

    .m-b-md {
        margin-bottom: 30px;
    }
    .block{
        display: inline-block;
        background-color: white;
    }
    .char{
        font-size: 30px;
        display: inline-block;
        padding: 8px;
        min-width: 37px;
        background-color: #FAFAFA;
        height: 100%;
        padding-top: 4px;
    }
    .dash{
        font-size: 22px;
        font-weight: bold;
        vertical-align: text-bottom;
        display: inline-block;
    }

    .div-input{
        margin-right: 10px;
    }
    .div-button button{
        height: 47px;
        width: 47px;
    }
    .div-input input{
        height: 47px;
        font-size: 22px;
        padding: 0 10px;
    }

    .guess{
        padding: 10px;
        margin-bottom: 25px;
    }

    .guess table{
        margin: auto;
    }





    @media (min-width: 1200px) {
      .char {
        font-size: 44px;
        padding:0px;
        min-width: 48px;
    }
    .dash{
        font-size: 22px;
    }}

    @media (max-width: 600px) {
      .char {
        font-size: 22px;
        padding:0px;
        min-width: 24px;
    }
    .dash{
        font-size: 16px;
    }}

    @media (max-width: 400px) {
      .char {
        font-size: 16px;
        padding:0px;
        width: 14px;
        min-width: 14px;
    }
    .dash{
        font-size: 10px;
    }
    .div-button button{
        height: 28px;
        width: 28px;
        padding: 0px;
    }
    .div-input input{
        height: 28px;
        font-size: 18px;
        padding: 0 8px;
    }}

    #guessResult{
        font-size: 31px;
    }
    .right{
        color: #24b324;
    }
    .wrong{
        color:red;
    }
    #extraInfo{
        max-width: 420px;
        word-wrap: break-word;
        text-align: center;
        margin: auto;
    }
</style>
</head>
<body>
    @if ($dev == true)
    <div class="content">


        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                @if(isset($who))
                <h3 title="The clues won't be *that* easy">Hi, {{ $who }}</h3>
                @endif

                <div class="guess">
                    <form id="frmGuess">
                        <table>
                            <tr>
                                <td>
                                    <div class="div-input">
                                    <input type="text" name="guess" autocomplete="off" {{$full?"disabled":""}}>
                                    </div>
                                </td>
                                <td>
                                    <div class="div-button">
                                        <button class="btn btn-default" {{$full?"disabled":""}}>:)</button>
                                    </div>
                                </td>
                            </tr>

                        </table>
                    </form>
                    <div id="guessResult" class="{{$full?"right":""}}">
                        @if($full == true)
                        It's all done!
                        @endif
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">


                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2 col-md-8 col-md-offset-2 col-sm-12 col-xs-12">

                        <div id="block1" class="block">
                            @if(isset($b[1]))
                            <div class="char 1">{{substr($b[1],0,1)}}</div>
                            <div class="char 2">{{substr($b[1],1,1)}}</div>
                            <div class="char 3">{{substr($b[1],2,1)}}</div>
                            <div class="char 4">{{substr($b[1],3,1)}}</div>
                            @else
                            <div class="char 1">&nbsp;</div>
                            <div class="char 2">&nbsp;</div>
                            <div class="char 3">&nbsp;</div>
                            <div class="char 4">&nbsp;</div>
                            @endif
                        </div>

                        <span class="dash">&mdash;</span>

                        <div id="block2" class="block">
                            @if(isset($b[2]))
                            <div class="char 1">{{substr($b[2],0,1)}}</div>
                            <div class="char 2">{{substr($b[2],1,1)}}</div>
                            <div class="char 3">{{substr($b[2],2,1)}}</div>
                            <div class="char 4">{{substr($b[2],3,1)}}</div>
                            @else
                            <div class="char 1">&nbsp;</div>
                            <div class="char 2">&nbsp;</div>
                            <div class="char 3">&nbsp;</div>
                            <div class="char 4">&nbsp;</div>
                            @endif
                        </div>

                        <span class="dash">&mdash;</span>


                        <div id="block3" class="block">
                            @if(isset($b[3]))
                            <div class="char 1">{{substr($b[3],0,1)}}</div>
                            <div class="char 2">{{substr($b[3],1,1)}}</div>
                            <div class="char 3">{{substr($b[3],2,1)}}</div>
                            <div class="char 4">{{substr($b[3],3,1)}}</div>
                            @else
                            <div class="char 1">&nbsp;</div>
                            <div class="char 2">&nbsp;</div>
                            <div class="char 3">&nbsp;</div>
                            <div class="char 4">&nbsp;</div>
                            @endif
                        </div>
                        <div id="extraInfo">

                        </div>

                    </div>
                </div>


            </div>
        </div>
    </div>

    @else
    <div class="content">
        <div class="title" style="color: #B6C5CC;">
            :)
        </div>

    </div>
    @endif

</body>
</html>
