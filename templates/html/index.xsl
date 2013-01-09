<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml" xmlns:file="http://xml.phpdox.de/src#"
    xmlns:cfg="http://phpdox.de/config"
    xmlns:html="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="#default file">
    
    <xsl:import href="topbar.xsl" />

    <xsl:output method="xml" indent="yes" encoding="utf-8" />

    <xsl:variable name="project" select="phe:getProjectNode()"/>

    <xsl:template match="/">
        <html class="no-js" lang="en">
            <head>
                <meta charset="utf-8" />
                <title><xsl:value-of select="$project/@name" /> - API Documentation</title>
                <link href="css/normalize.css" rel="stylesheet" type="text/css" media="all" />
                <link href="css/styles.css" rel="stylesheet" type="text/css" media="all" />
                <!-- next comment for IE-performance -->
                <!--[if lte IE 8]><![endif]-->
                <!--[if lte IE 8]><link href="css/oldie.css" rel="stylesheet" type="text/css" media="all" /><![endif]-->
            </head>

            <body>

                <div class="wrapper clearfix">
                    <div class="topbar clearfix">
                        <h1><a class="brand" href="./index.html"><xsl:value-of select="$project/@name" /> - API Documentation</a></h1>
                        <span style="float:right; padding-right:10px;">
                            <a href="#classes">Classes</a> | <a href="#interfaces">Interfaces</a> | <a href="#traits">Traits</a>
                        </span>
                    </div>

                    <div class="indexcontent">
                        <h2><xsl:value-of select="$project/@name" /></h2>
                        <p>Welcome to the API documentation page. Please select one of the listed classes, interfaces or traits to learn more about the indivdual item. You can navigate back to this page by use of the top navigation bar.</p>

                        <a name="classes" />
                        <h3>Classes</h3>
                        <xsl:choose>
                            <xsl:when test="//file:class">
                                <xsl:apply-templates select="//file:namespace[file:class]" mode="class">
                                    <xsl:sort select="@name" order="ascending" />
                                </xsl:apply-templates>
                            </xsl:when>
                            <xsl:otherwise>
                                <span style="color:#aaa">No classes defined</span>
                            </xsl:otherwise>
                        </xsl:choose>

                        <div class="clearfix" />

                        <a name="interfaces" />
                        <h3 class="separator">Interfaces</h3>
                        <xsl:choose>
                            <xsl:when test="//file:interface">
                                <xsl:apply-templates select="//file:namespace[file:interface]" mode="interface">
                                    <xsl:sort select="@name" order="ascending" />
                                </xsl:apply-templates>
                            </xsl:when>
                            <xsl:otherwise>
                                <span style="color:#aaa">No interfaces defined</span>
                            </xsl:otherwise>
                        </xsl:choose>

                        <div class="clearfix" />

                        <a name="traits" />
                        <h3 class="separator">Traits</h3>
                        <xsl:choose>
                            <xsl:when test="//file:trait">
                                <xsl:apply-templates select="//file:namespace[file:trait]" mode="trait">
                                    <xsl:sort select="@name" order="ascending" />
                                </xsl:apply-templates>
                            </xsl:when>
                            <xsl:otherwise>
                                <span style="color:#aaa">No traits defined</span>
                            </xsl:otherwise>
                        </xsl:choose>

                        <div class="footer"><xsl:value-of select="phe:info()" /></div>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="file:namespace">
        <h4>
            <xsl:choose>
                <xsl:when test="not(@name = '')"><xsl:value-of select="@name" /></xsl:when>
                <xsl:otherwise>/</xsl:otherwise>
            </xsl:choose>
        </h4>
    </xsl:template>

    <xsl:template match="file:namespace" mode="class">
        <div class="linkbox">
        <xsl:apply-templates select="." />
        <ul class="linklist">
            <xsl:apply-templates select="file:class">
                <xsl:sort select="@name" order="ascending" />
            </xsl:apply-templates>
        </ul>
        </div>
    </xsl:template>

    <xsl:template match="file:namespace" mode="interface">
        <xsl:apply-templates select="." />
        <ul class="linklist">
            <xsl:apply-templates select="file:interface">
                <xsl:sort select="@name" order="ascending" />
            </xsl:apply-templates>
        </ul>
    </xsl:template>

    <xsl:template match="file:namespace" mode="trait">
        <xsl:apply-templates select="." />
        <ul class="linklist">
            <xsl:apply-templates select="file:trait">
                <xsl:sort select="@name" order="ascending" />
            </xsl:apply-templates>
        </ul>
    </xsl:template>

    <xsl:template match="file:class|file:interface|file:trait">
        <li>
            <xsl:variable name="link"><xsl:choose>
                    <xsl:when test="local-name(.) = 'class'">classes</xsl:when>
                    <xsl:when test="local-name(.) = 'interface'">interfaces</xsl:when>
                    <xsl:otherwise>traits</xsl:otherwise>
                </xsl:choose>/<xsl:value-of select="translate(../@name, '\', '_')" /><xsl:if test="not(../@name = '')">_</xsl:if><xsl:value-of select="@name" />.<xsl:value-of select="$extension" /></xsl:variable>
            <a href="{$link}"><xsl:value-of select="@name" /></a>
        </li>
    </xsl:template>


</xsl:stylesheet>