<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:pdx="http://xml.phpdox.net/src"
                xmlns:pdxf="http://xml.phpdox.net/functions"
                xmlns:pu="http://schema.phpunit.de/coverage/1.0"
                xmlns:func="http://exslt.org/functions"
                xmlns:idx="http://xml.phpdox.net/src"
                xmlns:git="http://xml.phpdox.net/gitlog"
                xmlns:ctx="ctx://engine/html"
                extension-element-prefixes="func"
                exclude-result-prefixes="idx pdx pdxf pu git ctx">

    <xsl:import href="components.xsl" />
    <xsl:import href="functions.xsl" />

    <xsl:variable name="ctx" select="//pdx:dir[@ctx:engine]" />

    <xsl:output method="xml" indent="yes" encoding="UTF-8" doctype-system="about:legacy-compat" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head" />
            <body>
                <xsl:call-template name="nav" />

                <div id="mainstage">

                    <xsl:call-template name="breadcrumb" />

                    <h1>Source of <xsl:value-of select="$project" /></h1>
                    <p>
                        This project consists of <xsl:value-of select="count(//pdx:dir)" /> directories, containing
                        a total of <xsl:value-of select="count(//pdx:file)" /> files.
                    </p>

                    <ul class="path">
                        <li><a href="{$base}source/index.{$extension}">Source</a></li>
                        <xsl:apply-templates select="$ctx/parent::pdx:dir" mode="head" />
                        <xsl:if test="not($ctx/parent::pdx:source)">
                            <li class="separator">&#160;<xsl:value-of select="$ctx/@name" /></li>
                        </xsl:if>
                    </ul>
                    <table class="styled directory">
                        <tr>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Last Modified</th>
                        </tr>

                        <xsl:apply-templates select="$ctx/pdx:file|$ctx/pdx:dir" mode="table">
                            <xsl:sort select="@name" order="ascending" />
                        </xsl:apply-templates>

                        <tr>
                            <td colspan="3">
                                <small>
                                    <xsl:variable name="dircount" select="count($ctx/pdx:dir)" />
                                    <xsl:variable name="filecount" select="count($ctx/pdx:file)" />
                                    Total: <xsl:if test="$dircount &gt; 0"><xsl:value-of select="$dircount" /> directories,</xsl:if>
                                    <xsl:if test="$filecount &gt; 0"><xsl:value-of select="$filecount" /> files</xsl:if>
                                </small>
                            </td>
                        </tr>
                    </table>
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
                <xsl:apply-templates select="$ctx/parent::pdx:dir" mode="head"/>
                <li class="separator"><xsl:value-of select="$ctx/@name" /></li>
            </ul>
        </div>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template match="pdx:dir" mode="head">
        <xsl:apply-templates select="parent::pdx:dir" mode="head" />
        <xsl:if test="not(local-name(parent::*) = 'source')">
            <xsl:variable name="link">
                <xsl:for-each select="ancestor-or-self::pdx:dir[not(parent::pdx:source)]">
                    <xsl:value-of select="concat(@name, '/')" />
                </xsl:for-each>
            </xsl:variable>
           <li class="separator"><a href="{$base}source/{$link}index.{$extension}"><xsl:value-of select="@name" /></a></li>
        </xsl:if>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template match="pdx:dir" mode="table">
        <tr>
            <td><a href="{@name}/index.{$extension}"><strong><xsl:value-of select="@name" /></strong></a></td>
            <td>&#160;</td>
            <td>&#160;</td>
        </tr>
    </xsl:template>

    <xsl:template match="pdx:file" mode="table">
        <tr>
            <td><a href="{@name}.{$extension}"><xsl:value-of select="@name" /></a></td>
            <td><xsl:value-of select="pdxf:filesize(@size)" /></td>
            <td><xsl:value-of select="@time" /></td>
        </tr>
    </xsl:template>

</xsl:stylesheet>
