<!DOCTYPE html>
<html>
    <head>
        <title>GA Client</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />


        <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.1/jquery.fancybox.css" type="text/css" media="screen" />
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>

        <script type="text/javascript">

            $.postJSON = function(url, data, callback) { $.post(url, data, callback, "json"); };

            var worker = new Worker('ga-worker.js');

            worker.addEventListener('message', function(e) {
              if (!console.log) return;
              if (typeof e.data === 'string') {
                console.log('Worker said: ', e.data);
              } else {
                console.log(e.data);
                Object.getOwnPropertyNames(e.data).forEach(function(param) {
                    switch (param) {
                        case 'population':
                            $.postJSON('server.php', {action: 'workload', population: e.data.population}, function(data) {
                                console.log(data);
                            });
                        break;
                    }
                });
              }

            }, false);

            $.postJSON('server.php', {action: 'start'}, function(r) {
                worker.postMessage(r);
            });



        </script>

    </head>
    <body>


    </body>
</html>
