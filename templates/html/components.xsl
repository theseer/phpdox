<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:idx="http://xml.phpdox.de/src#">

    <xsl:param name="base" select="''" />
    <xsl:param name="xml" select="''" />
    <xsl:param name="extension" select="'xhtml'" />

    <xsl:template name="head">
        <xsl:param name="title" select="'Overview'" />

        <head>
            <title>phpDox - <xsl:value-of select="$title" /></title>
            <link rel="stylesheet" type="text/css" href="{$base}css/style.css" media="screen" />
        </head>

    </xsl:template>

    <xsl:template name="nav">
        <xsl:variable name="index" select="document(concat($xml,'index.xml'))/idx:index" />
        <nav class="topnav">
            <ul>
                <li>
                    <div class="logo"><span>/**</span>phpDox</div>
                </li>
                <li class="separator"><a href="{$base}index.xhtml">Overview</a></li>
                <xsl:if test="count($index/idx:namespace) &gt; 1">
                    <li class="separator"><a href="{$base}namespaces.xhtml">Namespaces</a></li>
                </xsl:if>
                <xsl:if test="count($index//idx:interface) &gt; 0">
                    <li><a href="{$base}interfaces.xhtml">Interfaces</a></li>
                </xsl:if>
                <xsl:if test="count($index//idx:class) &gt; 0">
                    <li><a href="{$base}classes.xhtml">Classes</a></li>
                </xsl:if>
                <xsl:if test="count($index//idx:trait) &gt; 0">
                    <li><a href="{$base}traits.xhtml">Traits</a></li>
                </xsl:if>
                <li class="separator"><a href="{$base}reports.xhtml">Reports</a></li>
            </ul>
        </nav>
    </xsl:template>

    <xsl:template name="footer">
        <footer>
            <span>Generated using phpDox 0.5-80-gae7d70b-dirty · Copyright (C) 2010 - 2013 by Arne Blankerts</span>
        </footer>
    </xsl:template>
</xsl:stylesheet>