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
    <xsl:output method="xml" indent="yes" encoding="UTF-8" doctype-system="about:legacy-compat" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head" />
            <body>
                <xsl:call-template name="nav" />
                <div id="mainstage">
                    <h1>Namespaces</h1>
                    <div class="container">
                        <h2>Namespaces</h2>
                        <table class="styled">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Interfaces</th>
                                <th>Classes</th>
                                <th>Traits</th>
                            </tr>
                        </thead>
                        <xsl:apply-templates select="//idx:namespace">
                            <xsl:sort select="@name" order="ascending" />
                        </xsl:apply-templates>
                        </table>
                    </div>
                </div>
                <xsl:call-template name="footer" />
            </body>
        </html>
    </xsl:template>

    <xsl:template match="idx:namespace">
        <tr>
            <td>\<xsl:value-of select="@name" /></td>
            <td class="nummeric">
                <xsl:call-template name="countlink">
                    <xsl:with-param name="ctx" select="idx:interface" />
                    <xsl:with-param name="mode" select="'interfaces'" />
                </xsl:call-template>
            </td>
            <td class="nummeric">
                <xsl:call-template name="countlink">
                    <xsl:with-param name="ctx" select="idx:class" />
                    <xsl:with-param name="mode" select="'classes'" />
                </xsl:call-template>
            </td>
            <td class="nummeric">
                <xsl:call-template name="countlink">
                    <xsl:with-param name="ctx" select="idx:trait" />
                    <xsl:with-param name="mode" select="'traits'" />
                </xsl:call-template>
            </td>
        </tr>
    </xsl:template>

    <xsl:template name="countlink">
        <xsl:param name="ctx" />
        <xsl:param name="mode" />

        <xsl:variable name="count" select="count($ctx)" />

        <xsl:choose>
            <xsl:when test="$count &gt; 0">
                <a href="{$base}{$mode}.{$extension}#{translate(@name, '\', '_')}"><xsl:value-of select="$count" /></a>
            </xsl:when>
            <xsl:otherwise>0</xsl:otherwise>
        </xsl:choose>
    </xsl:template>


</xsl:stylesheet>
