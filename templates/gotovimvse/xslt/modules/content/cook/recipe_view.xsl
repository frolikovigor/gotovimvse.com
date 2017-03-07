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

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:php="http://php.net/xsl"
                xmlns:date="http://exslt.org/dates-and-times"
                xmlns:udt="http://umi-cms.ru/2007/UData/templates"
                xmlns:umi="http://www.umi-cms.ru/TR/umi"
                extension-element-prefixes="php"
                exclude-result-prefixes="xsl php udt date">
    <xsl:output method="html" version="4.01"
                encoding="utf-8"
                doctype-public="-//W3C//DTD HTML 4.01//EN"
                doctype-system="http://www.w3.org/TR/html4/strict.dtd"
                indent="yes"
                cdata-section-elements="script noscript"
                undeclare-namespaces="yes"
                omit-xml-declaration="yes"/>

<xsl:template match="result[page/@type-id = 66]">
    <div id="content" class="clearfix">
		<xsl:call-template name="top_panel" />
		<div id="left-area" class="clearfix">
			<h1 class="title">
				<xsl:choose>
					<xsl:when test="$user-id = 2"><a href="/admin/content/edit/{$document-page-id}/" target="_blank"><xsl:value-of select="//property[@name='h1']/value" /></a></xsl:when>
					<xsl:otherwise><xsl:value-of select="//property[@name='h1']/value" /></xsl:otherwise>
				</xsl:choose>
			</h1>
            <p><xsl:value-of select="//property[@name='meta_descriptions']/value" /></p>
            <div class="single-imgs">
                <div class="single-img-box">
                    <div class="recipe-single-img">
						<xsl:choose>
							<xsl:when test="//property[@name='id_old_base']/value">
								<img src="/recipes/{//property[@name='id_old_base']/value}/img_500.jpg" alt="Image" />
							</xsl:when>
							<xsl:otherwise>
								<img src="/recipes/{$document-page-id}/img_500.jpg" alt="Image" />
							</xsl:otherwise>
						</xsl:choose>
                    </div>
                </div>
            </div>

            <div class="recipe-tags recipe-info">
                <img src="/templates/gotovimvse/images/clock_25.png" /> <span><b><xsl:value-of select="document(concat('udata://content/tr_time/',//property[@name='total_time']/value))" /></b></span>
                <img src="/templates/gotovimvse/images/like.png" />
                <span><b>
                    <xsl:choose>
                        <xsl:when test="//property[@name='likes']/value"><xsl:value-of select="//property[@name='likes']/value" /></xsl:when>
                        <xsl:otherwise>0</xsl:otherwise>
                    </xsl:choose></b>
                </span>
                <xsl:choose>
					<xsl:when test="//property[@name='views']/value"><span>Просмотров: <b><xsl:value-of select="//property[@name='views']/value" /></b></span></xsl:when>
					<xsl:otherwise><span>Просмотров: <b>0</b></span></xsl:otherwise>
				</xsl:choose>


                <span>Калорийность: <b><xsl:value-of select="//property[@name='calories_portion']/value" /> кКал/порцию, <xsl:value-of select="//property[@name='calories_sto']/value" /> кКал/100г</b></span>
            </div>

            <span class="w-pet-border"></span>

            <xsl:variable name="receipt_prods_udata" select="document(concat('udata://content/receipt_prods/',$document-page-id))" />
            <div id="ingredients" recipe_id="{$document-page-id}">
                <h3 class="blue">Ингредиенты</h3>
                <div id="list_ingredients">
                    <xsl:call-template name="receipt_prods">
                        <xsl:with-param name="receipt_prods_udata" select="$receipt_prods_udata" />
                    </xsl:call-template>
                </div>
                <div id="portions">
                    Порций:
                    <input type="text" value="{//property[@name='num_servings']/value}" onchange="recount_ingredients($(this).val());" disabled="disabled" />
                    <a href="javascript:void(0);" class="qty-minus disable" onClick="qtyCount('1', jQuery(this));" style=""></a>
                    <a href="javascript:void(0);" class="qty-plus" onClick="qtyCount('1', jQuery(this));"></a>
                </div>

                <div class="clearfix"></div>
            </div>

            <div class="glas_media">
                <ul>
                    <xsl:apply-templates select="$receipt_prods_udata//polls_glas_media//poll" mode="glas_media_prods" />
                </ul>
            </div>

            <span class="w-pet-border"></span>
            <div style="margin: 25px auto; width: 336px;">
                <xsl:value-of select="$main-page//property[@name='adsense_336x280']/value" disable-output-escaping="yes" />
            </div>
            <span class="w-pet-border"></span>

            <!--<h3 class="blue">Приготовление</h3>-->
            <div class="recipe_content">
                <xsl:value-of select="document(concat('udata://content/getRecipeContent/',$document-page-id))//udata" disable-output-escaping="yes" />
            </div>
            <br />

            <span class="w-pet-border"></span>
            <div style="margin: 25px auto;">
                <xsl:value-of select="$main-page//property[@name='adsense_336x280']/value" disable-output-escaping="yes" />

                
                <script async="async" src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                <!-- Блок с объявлениями -->
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-6489731360178090"
                     data-ad-slot="1035711052"
                     data-ad-format="autorelaxed"></ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
                <div class="clearfix"></div>
            </div>

            <span class="w-pet-border"></span>

            <div class="clearfix"></div>

			<xsl:call-template name="hyper_comments" />

            <div class="clearfix"></div>

            <!--Релевантные рецепты -->
            <div class="whats-hot">
                <h2 class="w-bot-border">Смотрите также</h2>
                <ul class="cat-list clearfix">
                    <xsl:apply-templates select="document(concat('udata://content/list_relevant_recipes/',$document-page-id,'/',15))" mode="root_category_recipes" />
                </ul>
            </div><!-- end of whats-hot div -->

        </div><!-- end of left-area -->
        <!-- LEFT AREA ENDS HERE -->


        <!-- ========== START OF SIDEBAR AREA ========== -->
		<div id="sidebar">
            <xsl:call-template name="sidebar" />
		</div><!-- end of sidebar -->

        <!-- ========== END OF SIDEBAR AREA ========== -->
        <div class="clearfix"></div>

        <div id="last_recipes">
            <div id="block1">
                <div class="sub_title">Последние рецепты<a href="/cook/rec/">Смотреть все</a></div>
                <ul class="cat-list clearfix">
                    <xsl:apply-templates select="document('udata://content/getLastElements/66/8/24911/pos/isnull')//item" mode="root_category_recipes" />
                </ul>
            </div>
        </div>

        <span class="w-pet-border"></span>
        <div style="margin: 25px auto; width: 728px;">
            <xsl:value-of select="$main-page//property[@name='adsense_728x90_2']/value" disable-output-escaping="yes" />
        </div>
        <span class="w-pet-border"></span>

        <xsl:call-template name="vkontakte_groupe" />
        <xsl:call-template name="facebook_groupe" />

        <div class="clearfix"></div>

	</div><!-- end of content div -->

</xsl:template>

<xsl:template match="item" mode="receipt_ingred">
    <li>
		<a href="{@link}"><xsl:value-of select="@ingredient" disable-output-escaping="yes" /></a>
		<span>
			<xsl:value-of select="@amount"/><xsl:value-of select="@ed"/>
			<xsl:if test="@amount_g">
				<span>(<xsl:value-of select="@amount_g" /> г)</span>
			</xsl:if>
		</span>
	</li>
</xsl:template>

<xsl:template match="item" mode="listCurrency">
	<xsl:if test="@active = 1">
        <option value="{@id}">
            <xsl:if test="@default = 1">
                <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:value-of select="@shot" disable-output-escaping="yes" />
        </option>
    </xsl:if>
</xsl:template>

<xsl:template match="udata[@method='receipt_prods']">
	<xsl:call-template name="receipt_prods">
		<xsl:with-param name="receipt_prods_udata" select="//udata" />
	</xsl:call-template>
</xsl:template>


<xsl:template name="receipt_prods">
	<xsl:param name="receipt_prods_udata" />
	<xsl:variable name="num_ingredients_half" select="ceiling(count($receipt_prods_udata//item) div 2)" />

	<div class="list-left">
		<ul>
			<xsl:apply-templates select="$receipt_prods_udata//item[position() &lt; ($num_ingredients_half+1)]" mode="receipt_ingred" />
		</ul>
	</div>

	<div class="list-right">
		<ul>
			<xsl:apply-templates select="$receipt_prods_udata//item[position() &gt; $num_ingredients_half]" mode="receipt_ingred" />
		</ul>
	</div>

	<div class="clearfix"></div>

	<div id="recipe_info">
		<div class="price"><xsl:value-of select="$receipt_prods_udata//price" />
			<select id="select_currency">
				<xsl:apply-templates select="document('udata://content/listCurrency')//item" mode="listCurrency" />
			</select>
		</div>
        <div class="weight">Вес: <xsl:value-of select="$receipt_prods_udata//weight" /> г, <xsl:value-of select="$receipt_prods_udata//weight_portion" /> г/порцию</div>
	</div>
</xsl:template>


    <xsl:template match="poll" mode="glas_media_prods">
        <li><a href="{.//url}" target="_blank"><xsl:value-of select=".//title" disable-output-escaping="yes" /></a></li>
    </xsl:template>
</xsl:stylesheet>