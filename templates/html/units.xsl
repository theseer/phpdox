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
                        <xsl:if test="$mode = 'class'">
                            <th />
                        </xsl:if>
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
                            <xsl:if test="$mode = 'class'">
                                <td>
                                    <xsl:call-template name="buildstate">
                                        <xsl:with-param name="class" select="." />
                                    </xsl:call-template>
                                </td>
                            </xsl:if>
                        </tr>
                    </xsl:for-each>
                </tbody>
            </table>
        </div>
    </xsl:template>

    <xsl:template name="buildstate">
        <xsl:param name="class" />

        <xsl:choose>
            <xsl:when test="$class//pu:coverage/@coverage != 0 or $class//pu:coverage/@executable != 0">
                <xsl:variable name="result" select="$class//pu:result" />
                <xsl:choose>
                    <!-- all 0 or skipped or incomplete -->
                    <xsl:when test="sum($result/@*) = 0 or $result/@skipped != 0 or $result/@incomplete != 0">
                        <xsl:attribute name="class">testresult-SKIPPED</xsl:attribute>UNTESTED</xsl:when>

                    <!-- at least one is failure or error-->
                    <xsl:when test="$result/@failure != '0' or $result/@error != '0'">
                        <xsl:attribute name="class">testresult-FAILED</xsl:attribute>FAILED</xsl:when>

                    <!-- everything 0 except passed -->
                    <xsl:when test="sum($result/@*) = $result/@passed and $result/@passed != 0">
                        <xsl:attribute name="class">testresult-PASSED</xsl:attribute>PASSED</xsl:when>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <!-- interfaces, empty classes and/or absence of coverage data -->
                <xsl:attribute name="class">testresult-EMPTY</xsl:attribute>EMPTY
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>

</xsl:stylesheet>
