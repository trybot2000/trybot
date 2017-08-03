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
    <script src="{{ URL::asset('js/who.js') }}"></script>

    <link rel="stylesheet" href="{{ URL::asset('css/app.css') }}">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #eee;
            color: #636b6f;
            font-family: sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
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
            margin-top: 15%;
        }

        table{
            text-align: center;
            margin: auto;
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

        @media (max-width: 400px) {
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

        </style>
    </head>
    <body>
        <div class="content">


            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div>
                        @if(isset($who))
                        <h3>Hi, {{ $who }}</h3>
                        <h4>Want to change your name?</h4>
                        @else
                        <h3>Who are you?</h3>
                        @endif
                    </div>
                    <form id="frmName" method="post">
                        <div class="name">
                            <table>
                                <tr>
                                    <td>
                                        <div class="div-input">
                                            <input type="text" name="name" autocomplete="off">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="div-button">
                                            <button class="btn btn-default">→</button>
                                        </div>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </form>
                </div>
            </div>



        </div>



    </body>
    </html>
