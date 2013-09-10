  <!-- JavaScript at the bottom for fast page loading: http://developer.yahoo.com/performance/rules.html#js_bottom -->
  <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if necessary -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js" type='text/javascript'></script>
  <script>window.jQuery || document.write('<script src="../../vendor/assets/javascripts/jquery-1.10.1.min.js">\x3C/script>')</script>
  <!--Add $translateWebsite -> facebookLoginCode() as code to make available in different lagnauges-->
  <!--[if lt IE 9]>
    <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
  <![endif]-->
  <script src='/app/assets/javascripts/_shared.min.js' type='text/javascript'></script>
  <script src='/app/assets/javascripts/<?php echo $file_name ?>.js' type='text/javascript'></script>
  <?php 
    if (isset($load_JS)) { 
      foreach ($load_JS as $js) {
        echo "<script src='/app/assets/javascripts/$js.js' type='text/javascript'></script>";
      }
    }
  ?>

  <!-- Asynchronous Google Analytics snippet. Change UA-XXXXX-X to be your site's ID.
       mathiasbynens.be/notes/async-analytics-snippet -->
  <script type='text/javascript'>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-XXXXXXXX-1', 'example.com');
    ga('send', 'pageview');

  </script>
</body>
</html>
