	<div id='footer-wrapper'>
		<footer>
			<div class='section contact active'>
				<h2 span style="color:#3d3d3d; text-shadow: 1px 1px 0px #fff;">Wildfire Health Updates</h2>
					<form action='emailconfirm.php' id='mailing-list' method='post'>
					<input name='f_email' type='text' placeholder='Email Address' id='f_email'  />	
					<input type='submit' id='wildfire-mailing-list' value='Submit' />
					</form>
				<p>Sign up to get the latest info &hellip;</p>
			</div><!--Close section contact active-->
		<div class='section' id='shopping'>
			<h3>Shopping</h3>
				<ul>
					<li><a href="productlist.php">Wildfire Store</a></li>
					<li><a href="shippinginfo.php">Shipping Info</a></li>
					<li><a href="contact-us.php">Contact Us</a></li>
				</ul>
		</div><!--Close section more info-->
		
		<div class='section' id='stores'>
			<h3>Get Started</h3>
				<ul>
					<li><a href="aboutus.php">About Us</a></li>
					<li><a href="resources.php">Resources</a></li>
					<li><a href="/blog">Wildfire Blog</a></li>
				</ul>
		</div><!--Close section stores-->
  
		<div class='section' id='social'>
			<h3>Find Us:</h3>
				<div class='fbsocial'><a href="" class="fbsmallicon"></a></div>
				<div class='twittersocial'><a href="" class="twittersmallicon"></a></div>
		</div><!--Close section social-->

		<div id='bottom'>
		<p class='left'><?php echo date('Y'); ?> Wildfire Health.</p>
		<p class='right'>
		<a class='email' href='mailto:info@wildfirehealth.com'>info@wildfirehealth.com</a>
		</p>
		</div><!--Close bottom-->
	</footer>

    </div><!--/footer-wrapper-->