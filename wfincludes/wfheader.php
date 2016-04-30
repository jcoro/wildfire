
  <meta charset='utf-8' />
  <meta content='IE=edge,chrome=1' http-equiv='X-UA-Compatible' />
  <meta content='' name='author' />
  <meta content='' name='description' />
  <meta content='width=1024' name='viewport' />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="Description" content="<?php echo ((isset($_ENV["request.cwpage"]["description"])) ? $_ENV["request.cwpage"]["description"] : "" ); ?>">
  <?php // CARTWEAVER CSS ?>
  <link href="cw4/css/cw-core.css" rel="stylesheet" type="text/css"> 
  <link href='/img/favicon.png' rel='shortcut icon' />
  <link href='Scripts/Style2.css' rel='stylesheet' />
  <link href='Scripts/Boilerplate.css' rel='stylesheet' />
  
  <!-- end CSS -->
  <!-- end CSS -->
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
  <script src="Scripts/slide_show.js"></script>
  <script src="Scripts/javascriptmenu.js"></script>
  

  <!-- All plugins -->

</head>
<body class="index" data-context-parent="" data-context="">


  <!-- Header -->
  <header>
    <div class='header-inner-hp'>

  <div id='right'>
    <div class='right-inner'>

   <div class="top-ad"><a href=""><img src="Images/testadsmall.png" width="468" height="60"></a></div>


            <nav id='topnav'>
      
      <div id="topcartnav">
<?php
// cart links, log in links, alerts 
include("cw4/cwapp/inc/cw-inc-pagestart.php");
?>


</div>

 <span>
 <a class='item_new' href=''>About Us</a>
 </span>
 
 
<span>
 <div class='menu-hover-wrapper-background'>
  <div class='menu-hover-wrapper-bar'></div>
  <ul class='dropdown'>
     <!--<li><a href=''>Consulting</a></li>-->
     <li><a href="productlist.php">Supplements</a></li>
      <!--<li><a href=''>Gift Cards</a></li>-->
  </ul>
</div>
<a class='item_shop' href='productlist.php'>Shop</a>
</span>

<span>
<!--//Resources Dropdown List
<div class='menu-hover-wrapper-background'>

  <div class='menu-hover-wrapper-bar'></div>

  <ul class='dropdown'>
  <li><a class='item_lookbook' href=''>CRON-O-METER</a></li>
  <li><a class='item_a_moment_with' href=''>Food Log</a></li>
  <li><a class='item_on_location' href=''>Grocery List</a></li>  
  <li><a class="item_magazine" href="">E-Books</a></li>
  </ul>

</div>
-->
<a class='item_features' href='/features'>Resources</a>

</span>

<span>
<!--//Learn More Dropdown List
<div class='menu-hover-wrapper-background'>
  <div class='menu-hover-wrapper-bar'></div>
  <ul class='dropdown'>
  <li><a href="/">
  FAQ
  </a></li>
    <li><a href="/">
        Articles
      </a></li><li><a href="/">
        Blog
      </a></li><li><a href="/">
        Advocacy
      </a></li>
  </ul>
</div>
-->
<a class='item_locations' href='/locations'>Learn More</a></span><span><a class='item_blog' href='/blog'>Blog</a></span>

