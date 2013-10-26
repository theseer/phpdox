<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:idx="http://xml.phpdox.de/src#"
                exclude-result-prefixes="idx">

    <xsl:import href="components.xsl" />

    <xsl:output method="xml" indent="yes" encoding="utf-8" />

    <xsl:variable name="unit" select="/*[1]" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head">
                <xsl:with-param name="title" select="'ClassX'" />
            </xsl:call-template>
            <body>
                <xsl:call-template name="nav" />
                <div id="mainstage">
                    <xsl:call-template name="breadcrumb" />
                    <xsl:call-template name="sidenav" />
                    <section>
                        <h1><small>\<xsl:value-of select="$unit/@namespace" />\</small><xsl:value-of select="$unit/@name" /></h1>
                        <h4>The main Application class</h4>
                        <p>This class does all the kewl work.</p>
                    </section>
                </div>
                <xsl:call-template name="footer" />
            </body>
        </html>
    </xsl:template>


    <xsl:template name="breadcrumb">
        <div class="box">
            <ul class="breadcrumb">
                <li><a href="{$base}index.{$extension}">Overview</a></li>
                <li class="separator"><a href="{$base}classes.{$extension}">Classes</a></li>
                <li class="separator"><a href="{$base}classes.{$extension}#{translate($unit/@namespace, '\', '_')}">\<xsl:value-of select="$unit/@namespace" /></a></li>
                <li class="separator"><xsl:value-of select="$unit/@name" /></li>
            </ul>
        </div>
    </xsl:template>

    <xsl:template name="sidenav">
        <nav class="box">
            <ul>
                <li><a href="#">Introduction</a></li>
                <li><a href="#">Synopsis</a></li>
                <li><a href="#">Hierarchy</a></li>
                <li><a href="#">Coverage</a></li>
                <li><a href="#">Violations</a></li>
                <li><a href="#">Tasks</a></li>
                <li><a href="#">Constants</a></li>
                <li><a href="#">Members</a></li>
                <li><a href="#">Methods</a></li>
            </ul>
        </nav>
    </xsl:template>

</xsl:stylesheet>