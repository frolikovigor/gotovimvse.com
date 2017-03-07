<?xml version="1.0" encoding="UTF-8"?>

<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file"[
    <!ENTITY nbsp  "&#xA0;">
    <!ENTITY copy  "&#169;">
    <!ENTITY mdash "&#8212;">
    
    <!ENTITY laquo  "&#171;">
    <!ENTITY raquo  "&#187;">
    
    <!ENTITY rarr  "&#8594;">
    <!ENTITY larr  "&#8592;">  
]>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:output encoding="utf-8" method="html" indent="yes"/>
	
	<xsl:template match="/">
		<xsl:call-template name="redirect" />
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE HTML&gt;</xsl:text>
		<html>
			<xsl:variable name="getCssFile" select="document(concat('udata://content/getCssFile/',$document-page-id))" />
			<head>

				<title>
					<xsl:choose>
						<xsl:when test="$document-page-type-id = 67"><xsl:value-of select="$document-title" /> - рецепты с фото</xsl:when>
                        <xsl:when test="$document-page-type-id = 99">Гороскоп - <xsl:value-of select="document(concat('upage://',$parent-id))//property[@name='h1']/value" /> на <xsl:value-of select="//property[@name='h1']/value" />
                        </xsl:when>
                        <xsl:otherwise><xsl:value-of select="$document-title" /></xsl:otherwise>
					</xsl:choose>
				</title>
				<xsl:if test="$document-page-id=1"><meta name='yandex-verification' content='58db1e166dc01387' /></xsl:if>
				
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<meta name="keywords" content="{//meta/keywords}" />
				<meta name="description" content="{//meta/description}" />
				
				<link rel="icon" type="image/x-icon" href="/favicon.ico" />
				<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />				
				
				<!--<link rel="stylesheet" type="text/css" href="{$template-resources}fonts/orpheus.css?{/result/@system-build}" />-->
				<!--<link rel="stylesheet" type="text/css" href="{$template-resources}css/main.css?{/result/@system-build}" />-->
				<!--<link rel="stylesheet" type="text/css" href="{$template-resources}css/custom.css?{/result/@system-build}" />-->
				<!--<link rel="stylesheet" type="text/css" media="all" href="{$template-resources}css/style.css" />-->
				<!--<link rel="stylesheet" media="all" type="text/css" href="{$template-resources}js/prettyPhoto/css/prettyPhoto.css" />-->
				<!--<link rel="stylesheet" media="all" type="text/css" href="{$template-resources}css/jquery-ui-1.10.4.custom.min.css" />-->
				<!--<link rel="stylesheet" media="all" type="text/css" href="{$template-resources}js/SocialPanel/socializ.css" />-->

                <meta name="twitter:card" content="summary" />
                <meta name="twitter:site" content="@gotovimvse" />
                <meta property="og:url" content="https://gotovimvse.com{$page_link}" />
                <meta property="og:title">
                    <xsl:attribute name="content">
                        <xsl:choose>
                            <xsl:when test="$document-page-type-id = 67"><xsl:value-of select="$document-title" /> - рецепты с фото</xsl:when>
                            <xsl:when test="$document-page-type-id = 99">Гороскоп - <xsl:value-of select="document(concat('upage://',$parent-id))//property[@name='h1']/value" /> на <xsl:value-of select="//property[@name='h1']/value" />
                            </xsl:when>
                            <xsl:otherwise><xsl:value-of select="$document-title" /></xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </meta>
                <meta property="og:description" content="{//meta/description}" />


                <meta property="og:image">
                    <xsl:attribute name="content">
                        <xsl:choose>
                            <xsl:when test="//property[@name='id_old_base']/value">https://gotovimvse.com/recipes/<xsl:value-of select="//property[@name='id_old_base']/value"/>/img_500.jpg</xsl:when>
                            <xsl:otherwise>
                                <img src="https://gotovimvse.com/recipes/{$document-page-id}/img_500.jpg" alt="Image" />
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:attribute>
                </meta>


				<link type="text/css" rel="stylesheet" href="/min/f=templates/gotovimvse/fonts/orpheus.css,templates/gotovimvse/css/main.css,templates/gotovimvse/css/custom.css,templates/gotovimvse/css/style.css,templates/gotovimvse/js/prettyPhoto/css/prettyPhoto.css,templates/gotovimvse/css/jquery-ui-1.10.4.custom.min.css,templates/gotovimvse/js/SocialPanel/socializ.css" />

				<xsl:if test="$getCssFile//css">
					<link rel="stylesheet" media="all" type="text/css" href="{$getCssFile//css}" />
				</xsl:if>

                <xsl:if test="$document-page-id=1">
                    <!--<link rel="stylesheet" media="all" type="text/css" href="{$template-resources}js/smoothdivscroll/smoothDivScroll.css" />-->
                </xsl:if>
                <xsl:choose>
                    <xsl:when test="$user-type = 'sv'">
                        <xsl:value-of select="document('udata://system/includeQuickEditJs')/udata" disable-output-escaping="yes"/>
                        <xsl:value-of select="document('udata://system/includeEditInPlaceJs')/udata" disable-output-escaping="yes"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
                        <xsl:if test="$user-type != 'sv'">
                            <xsl:value-of select="$main-page//property[@name='google_analytics']/value" disable-output-escaping="yes" />
                        </xsl:if>
                    </xsl:otherwise>
                </xsl:choose>
			</head>
			
			<body>
			<div id="background">
				<xsl:if test="$getCssFile//existcurrentimage = 1">
                    <xsl:if test="$getCssFile//category">
                        <img id="first" src="{$getCssFile//category}image.jpg">
                            <xsl:if test="$getCssFile//previous">
                                <xsl:attribute name="style">display:none;</xsl:attribute>
                            </xsl:if>
                        </img>
                    </xsl:if>
                </xsl:if>

                <xsl:if test="$getCssFile//existpreviousimage = 1">
                    <xsl:if test="$getCssFile//previous">
                        <img id="second" src="{$getCssFile//previous}image.jpg" />
                    </xsl:if>
                </xsl:if>
			</div>


            <div id="popup">
                <img class="preloader_basket" src="{$template-resources}images/preloader_big.gif" />
                <div id="popup_inline"></div>
            </div>
            <a id="open_basket" rel="prettyPhotoBasket" href="#popup">Перейти в корзину</a>

			<!-- ============= HEADER STARTS HERE ============== -->
				<div id="header-wrapper">
	                <!-- NAVIGATION BAR STARTS HERE -->
			        <div id="nav-wrap">
						<div class="inn-nav clearfix">
		                        <!-- MAIN NAVIGATION STARTS HERE -->
								<xsl:apply-templates select="document('udata://content/menu/')//items" mode="main_navigation" />
								<!-- MAIN NAVIGATION ENDS HERE -->
						</div>
					</div><!-- end of nav-wrap -->
					<!-- NAVIGATION BAR ENDS HERE -->
				</div><!-- end of header-wrapper div -->

			<!-- ============= HEADER ENDS HERE ============== -->
			<!-- ============= CONTAINER STARTS HERE ============== -->
		        <div id="container">
			        <!-- ============= CONTENT AREA STARTS HERE ============== -->
			        <xsl:apply-templates select="result" />
			        <!-- CONTENT ENDS HERE -->
                </div><!-- end of container div -->
				<div class="w-pet-border"></div>
			<!-- ============= CONTAINER AREA ENDS HERE ============== -->

			<!-- ============= BOTTOM AREA STARTS HERE ============== -->
			<div id="bottom-wrap">
				<ul id="bottom" class="clearfix">
					<li class="block">
                        <xsl:call-template name="recipes_categories" />
					</li>
					<li class="block">
                        <xsl:call-template name="articles_categories" />
                        <div class="white_space"></div>
                        <a href="/cook/ingredients/"><h2>Продукты</h2></a>
                        <a href="/br/"><h2>Рецепты красоты</h2></a>
					</li>
					<li class="block">
                        <!--<xsl:call-template name="larimel_categories" />-->
					</li>
                    <li class="block">
                        <xsl:if test="$user-type != 'sv'">
                            <xsl:value-of select="$main-page//property[@name='liveinternet']/value" disable-output-escaping="yes" />
                            <xsl:value-of select="$main-page//property[@name='top_rambler']/value" disable-output-escaping="yes" />
                            <xsl:value-of select="$main-page//property[@name='mail_rating']/value" disable-output-escaping="yes" />
                        </xsl:if>
                        &nbsp;
                    </li>
				</ul>
			</div><!-- end of bottom-wrap div -->
			<!-- ============= BOTTOM AREA ENDS HERE ============== -->


			<!-- ============= FOOTER STARTS HERE ============== -->	
		        <div id="footer-wrap" >
					<div id="footer">
						<p class="copyright">© 2012 - <xsl:value-of select="document('udata://system/convertDate/now/(Y)')" />, <xsl:apply-templates select="document('udata://content/menu')//item[@id != 1]" mode="footer_title_site" /></p>
						<p class="dnd"><a href="mailto:admin@gotovimvse.com">admin@gotovimvse.com</a></p>
					</div><!-- end of footer div -->
				</div><!-- end of footer-wrapper div -->
			<!-- ============= FOOTER STARTS HERE ============== -->
			<!-- Remove it if you do not need jQuery -->
				<!--<script type="text/javascript" src="{$template-resources}js/custom.js?{/result/@system-build}"></script>-->
				<!--<script type="text/javascript" src="{$template-resources}js/main.js"></script>-->
			    <!--<script type="text/javascript" src="{$template-resources}js/jquery.cycle.js"></script>-->
			    <!--<script type="text/javascript" src="{$template-resources}js/jquery.easing.1.3.js"></script>-->
			    <!--<script type="text/javascript" src="{$template-resources}js/prettyPhoto/js/jquery.prettyPhoto.js"></script>-->
			    <!--<script type="text/javascript" src="{$template-resources}js/jquery-ui-1.8.16.custom.min.js"></script>-->
                <!--<script type="text/javascript" src="{$template-resources}js/jquery.lockfixed.min.js"></script>-->
				<!--<script type="text/javascript" src="{$template-resources}js/SocialPanel/socializ.js"></script>-->
				<!--<script type="text/javascript" src="{$template-resources}js/script.js"></script>-->

				<script type="text/javascript" src="/min/b=templates/gotovimvse/js&amp;f=custom.js,main.js,jquery.cycle.js,jquery.easing.1.3.js,prettyPhoto/js/jquery.prettyPhoto.js,jquery-ui-1.8.16.custom.min.js,jquery.lockfixed.min.js,SocialPanel/socializ.js,script.js"></script>

                <xsl:if test="$getCssFile//category">
                    <script type="text/javascript" src="{$getCssFile//category}script.js"></script>
                </xsl:if>
                <xsl:if test="$document-page-id=1">
                    <script type="text/javascript" src="{$template-resources}js/smoothdivscroll/jquery.mousewheel.min.js"></script>
                    <script type="text/javascript" src="{$template-resources}js/smoothdivscroll/jquery.kinetic.min.js"></script>
                    <script type="text/javascript" src="{$template-resources}js/smoothdivscroll/jquery.smoothdivscroll-1.3-min.js"></script>
                </xsl:if>
				<script type="text/javascript">socializ(encodeURIComponent('<xsl:value-of select="concat('https://',$domain,$page_link)" />'),encodeURIComponent('<xsl:value-of select="//property[@name='h1']/value" />'))</script>


            </body>
		</html>
		
	</xsl:template>
	
</xsl:stylesheet>