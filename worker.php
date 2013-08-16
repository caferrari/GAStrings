<!DOCTYPE html>
<html>
    <head>
        <title>GA Client</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />


        <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.1/jquery.fancybox.css" type="text/css" media="screen" />
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>

        <script type="text/javascript">

            $(document).ready(function(){
                $.postJSON = function(url, data, callback) { $.post(url, data, callback, "json"); };

                var worker = new Worker('ga-worker.js');

                function htmlEntities(str) {
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                }

                worker.addEventListener('message', function(e) {
                  if (!console.log) return;
                  if (typeof e.data === 'string') {
                    console.log('Worker said: ', e.data);
                  } else {
                    Object.getOwnPropertyNames(e.data).forEach(function(param) {
                        switch (param) {
                            case 'population':
                                $.postJSON('server.php', {action: 'workload', population: e.data.population}, function(r) {
                                    worker.postMessage(r);
                                });
                            break;
                            case 'best':
                                $('#result').html(htmlEntities(e.data.best.gene));
                                $('#fitness').html('Fitness: ' + e.data.best.fitness + ' | ' + e.data.best.generations + ' Generations');
                            break;
                        }
                    });
                  }

                }, false);

                $.postJSON('server.php', {action: 'start'}, function(r) {
                    worker.postMessage(r);
                });
            });

        </script>

        <link href='http://fonts.googleapis.com/css?family=Ubuntu+Mono' rel='stylesheet' type='text/css'>
        <style type="text/css">
            div {
                font-family: 'Ubuntu Mono', sans-serif;
                word-break: break-all
            }

            #fitness {
                text-align: center;
                font-weight: bold;
                font-site: 3em;
                margin-bottom: 10px;
            }
        </style>

    </head>
    <body>

        <div id="fitness">

        </div>


        <div id="result">

        </div>


    </body>
</html>
