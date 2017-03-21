<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:pdx="http://xml.phpdox.net/src"
                xmlns:pdxf="http://xml.phpdox.net/functions"
                xmlns:pu="http://schema.phpunit.de/coverage/1.0"
                xmlns:func="http://exslt.org/functions"
                xmlns:idx="http://xml.phpdox.net/src"
                xmlns:git="http://xml.phpdox.net/gitlog"
                xmlns:ctx="ctx://engine/html"
                xmlns:token="http://xml.phpdox.net/token"
                xmlns:src="http://xml.phpdox.net/src"
                extension-element-prefixes="func"
                exclude-result-prefixes="idx pdx pdxf pu git ctx token src">

    <xsl:import href="components.xsl" />
    <xsl:import href="functions.xsl" />

    <xsl:param name="base" select="''" />

    <xsl:output indent="no" standalone="yes" method="xml" xml:space="default" />

    <xsl:template match="/">
        <html lang="en">
            <head>
                <title>phpDox - Source of <xsl:value-of select="//src:file/@file" /></title>
                <link rel="stylesheet" type="text/css" href="{$base}css/style.css" media="screen" />
                <link rel="stylesheet" href="{$base}css/source.css" />
                <meta http-equiv="content-type" content="text/html; charset=utf-8" />
            </head>

            <body>
                <xsl:call-template name="nav" />
                <div id="mainstage">

                    <xsl:call-template name="breadcrumb" />

                    <h1>Source of file <xsl:value-of select="//src:file/@file" /></h1>
                    <p>
                        Size: <xsl:value-of select="format-number(//src:file/@size, '0,000')" /> Bytes - Last Modified: <xsl:value-of select="//src:file/@time" />
                    </p>
                    <section>
                        <h2><small><xsl:value-of select="//src:file/@path" />/</small><xsl:value-of select="//src:file/@file" /></h2>
                        <xsl:call-template name="source" />
                    </section>
                </div>
                <xsl:call-template name="footer" />
            </body>
        </html>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="breadcrumb">
        <div class="box">
            <ul class="breadcrumb">
                <li><a href="{$base}index.{$extension}">Overview</a></li>
                <li class="separator"><a href="{$base}source/index.{$extension}">Source</a></li>


            </ul>
        </div>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="source">
        <table class="source">
            <tr>
                <td class="no">
                    <xsl:for-each select="//token:line">
                        <xsl:variable name="no" select="@no" />
                        <xsl:variable name="ctx" select="//src:enrichment[@type='phpunit']//pu:line[@nr = $no]" />
                        <xsl:variable name="coverage">
                            <xsl:if test="count($ctx/pu:covered) &gt; 0"> covered</xsl:if>
                        </xsl:variable>
                        <a class="anker{$coverage}" href="#line{@no}"><xsl:value-of select="@no" /></a>
                        <xsl:if test="count($ctx/pu:covered) &gt; 0">
                        <div class="coverage_details">
                            <span>
                                Covered by <xsl:value-of select="count($ctx/pu:covered)" /> test(s):
                            </span>
                            <ul>
                            <xsl:for-each select="$ctx/pu:covered">
                                <li><xsl:value-of select="@by" /></li>
                            </xsl:for-each>
                            </ul>
                        </div>
                        </xsl:if>
                    </xsl:for-each>
                </td>
                <td class="line">
                    <xsl:apply-templates select="//token:line" />
                </td>
            </tr>
        </table>
    </xsl:template>

    <xsl:template match="token:line[not(*)]">
        <div id="line{@no}"><br/></div>
    </xsl:template>

    <xsl:template match="token:line">
        <div id="line{@no}">
            <pre><xsl:apply-templates select="token:token" /></pre>
        </div>
    </xsl:template>

    <xsl:template match="token:token">
        <span class="token {@name}"><xsl:value-of select="." /></span>
    </xsl:template>

</xsl:stylesheet>
