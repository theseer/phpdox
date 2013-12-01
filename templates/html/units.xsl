<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:idx="http://xml.phpdox.net/src#"
                exclude-result-prefixes="idx">

    <xsl:import href="components.xsl" />

    <xsl:output method="xml" indent="yes" encoding="utf-8" />
    <xsl:param name="mode" select="'class'" />
    <xsl:param name="title" select="'Classes'" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head" />
            <body>
                <xsl:call-template name="nav" />
                <div id="mainstage">
                    <h1><xsl:value-of select="$title" /></h1>
                    <xsl:apply-templates select="//idx:namespace[*[local-name() = $mode]]">
                        <xsl:sort select="@name" order="ascending" />
                    </xsl:apply-templates>
                </div>
                <xsl:call-template name="footer" />
            </body>
        </html>
    </xsl:template>

    <xsl:template match="idx:namespace">
        <div class="container">
            <h2 id="{translate(@name, '\', '_')}">\<xsl:value-of select="@name" /></h2>
            <table class="styled">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <xsl:for-each select="*[local-name() = $mode]">
                        <xsl:sort select="@name" order="ascending" />

                        <xsl:variable name="link"><xsl:choose>
                            <xsl:when test="local-name(.) = 'class'">classes</xsl:when>
                            <xsl:when test="local-name(.) = 'interface'">interfaces</xsl:when>
                            <xsl:otherwise>traits</xsl:otherwise>
                        </xsl:choose>/<xsl:value-of select="translate(../@name, '\', '_')" /><xsl:if test="not(../@name = '')">_</xsl:if><xsl:value-of select="@name" />.<xsl:value-of select="$extension" /></xsl:variable>
                        <tr>
                            <td><a href="{$link}"><xsl:value-of select="@name" /></a></td>
                            <td>
                                <xsl:choose>
                                    <xsl:when test="@description"><xsl:value-of select="@description" /></xsl:when>
                                    <xsl:otherwise><span class="unavailable">No description available</span></xsl:otherwise>
                                </xsl:choose>
                            </td>
                            <td>[Build-State]</td>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </div>
    </xsl:template>

</xsl:stylesheet>