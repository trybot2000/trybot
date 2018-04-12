<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>API Help</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <style>
    html, body {
        background-color: #fff;
        color: #2C2C2C;
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

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        padding: 25px;
        /*  text-align: center;  */
    }

    .title {
        font-size: 84px;
    }

    .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }

    .m-b-md {
        margin-bottom: 30px;
    }
    ul{
        list-style-type: none;
    }
    li > div{
        padding-left: 15px;
    }
    code{
        background-color: #e8e8e8;
        color: #e20000;
        padding: 2px;
    }
</style>
</head>
<body>
    <div class="position-ref full-height">

        <div class="content">
            <h1>API Help</h1>
            Here's a quick and dirty explanation of what an API is and how to use it.
            <h2>Definitions</h2>
            <ul>
                <li>
                    <h3>API</h3>
                    <div>Application Programming Interface</div>
                </li>
                <li>
                    <h3>Base URL</h3>
                    <div>
                        This is the part of the API's URL that doesn't change. All of the Endpoints below are assumed to start with the Base URL.
                        <br />
                        <br />
                        For example, base URL <code>example.com/api</code> and the route <code>/hello</code> together make the full URL of <code>example.com/api/hello</code>
                    </div>
                </li>
                <li><h3>Route</h3>
                    <div>
                        The Route is the part of a URL after the Base URL. So for the endpoint <code>example.com/api/hello</code> the route is <code>/api/hello</code>
                    </div>
                </li>
                <li><h3>Endpoint</h3>
                    <div>
                        A Base URL plus a Route together make an endpoint. For example, an API might have an authentication endpoint of <code>example.com/api/auth</code>, which is made of the Base URL <code>example.com/api</code> and the Route <code>/auth</code>
                    </div>
                </li>

                <li><h3>Query Parameters</h3>
                    <div>
                        When you want to send additional info to an endpoint, you might add Query Parameters using <code>?</code>
                        <br />
                        <br />
                        For example, if the endpoint requires your name, a query string might be <code>?name=Jon</code>, making a full endpoint of <code>example.com/api/your_name?name=Jon</code>
                        <br />
                        <br />
                        Multiple query parameters in one URL are linked by <code>&</code> such as <code>example.com/api/your_name?first=Barack&last=Obama</code>
                    </div>
                </li>
                <li>
                    <h3>Method</h3>
                    When you have an endpoint, you need to know what to do with it. There are two main methods of "calling" an endpoint: <code>GET</code> and <code>POST</code>.
                    <h4>GET</h4>
                    When you type a URL into your browser and press enter, you've just sent a <code>GET</code> request. This is the easiest method to execute, since it's something we do many times a day without thinking about it. All of the information needed for a <code>GET</code> request is contained in the URL.
                    <h4>POST</h4>
                    If you have to send more information than can fit in a URL. For example, when you write a new tweet, clicking the "Tweet" button sends a <code>POST</code> request to <code>https://twitter.com/i/tweet/create</code>. The text of your tweet is in the <code>payload</code> of the <code>POST</code> request. Here's what the payload looks like:
                    <pre>
                        {
                            "authenticity_token": "7330c7d9c878e70fed32a3f83f55f56f7b0cf116",
                            "is_permalink_page": false,
                            "place_id": null,
                            "status": "My first tweet!",
                            "tagged_users": null,
                        }
                    </pre>
                    For now, don't worry about <code>POST</code> requests; just know that they exist.
                </li>
                <li><h3>JSON</h3>
                    JSON stands for JavaScript Object Notation. It sounds complicated, but it's really just a way to format data that's easy for humans <i>and</i> computers to read.
                    <br />
                    <br />
                    Example, as a comma-separated string:

                    <pre>
                        FirstName,LastName,Office
                        Barack,Obama,President
                        Joe,Biden,Vice President
                    </pre>
                    And the same information, in JSON format:
                    <pre>
                        [
                        {
                            "FirstName": "Barack",
                            "LastName": "Obama",
                            "Office": "President"
                        },
                        {
                            "FirstName": "Joe",
                            "LastName": "Biden",
                            "Office": "Vice President"
                        }
                        ]
                    </pre>

                    No need to worry about the exact syntax of JSON data, just know that if you see it, that's good. Most good API endpoints will return JSON data by default, because it's a format that's easy to read by almost any programming language with any extra work.
                </li>
                <li>
                    <h3>null</h3>
                    If you see <code>null</code> in a JSON response, that means "empty" or "nothing".
                </li>
            </ul>

            <h2>Endpoints</h2>
            Okay, so you've learned some terms, now it's time to see some example API endpoints and try them out.
            <ul>
                <li>
                    <h3>/</h3>
                    This is the <i>root</i> endpoint (it's the same as the base URL). 
                    <br />
                    Try it out: <a href="https://trybot2000.com/api/mostowy/" target="_blank">https://trybot2000.com/api/mostowy/s</a>
                </li>
                <li>
                    <h3>/auth</h3>
                </li>
                <li>
                    <h3>/help</h3>
                </li>
                <li>
                    <h3>/parrot</h3>
                </li>
                <li>
                    <h3>/math/add</h3>
                </li>
            </ul>
        </li>


    </div>
</div>
</body>
</html>
