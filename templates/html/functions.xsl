<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:func="http://exslt.org/functions"
                xmlns:pdxf="http://xml.phpdox.net/functions"
                extension-element-prefixes="func"
                exclude-result-prefixes="pdxf">

    <func:function name="pdxf:link">
        <xsl:param name="ctx" />
        <xsl:param name="method" />
        <xsl:param name="copy" />

        <xsl:variable name="dir">
            <xsl:choose>
                <xsl:when test="local-name($ctx) = 'implements'">interfaces</xsl:when>
                <xsl:when test="local-name($ctx) = 'uses'">traits</xsl:when>

                <xsl:when test="local-name($unit) = 'interface'">interfaces</xsl:when>
                <xsl:when test="local-name($unit) = 'class'">classes</xsl:when>
                <xsl:when test="local-name($unit) = 'trait'">traits</xsl:when>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="withMethod">
            <xsl:if test="$method != ''"><xsl:value-of select="concat('/', $method)" /></xsl:if>
        </xsl:variable>

        <xsl:variable name="link">
            <xsl:value-of select="concat($base, $dir, '/', translate($ctx/@full, '\', '_'), $withMethod, '.', $extension)" />
        </xsl:variable>

        <xsl:variable name="text">
            <xsl:choose>
                <xsl:when test="$copy"><xsl:value-of select="$copy" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="$ctx/@name" /></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <func:result><a title="{$ctx/@full}" href="{$link}"><xsl:value-of select="$text" /></a></func:result>
    </func:function>

    <func:function name="pdxf:nl2br">
            <xsl:param name="string"/>
            <xsl:variable name="format">
            <xsl:value-of select="normalize-space(substring-before($string,'&#10;'))"/>
            <xsl:choose>
                <xsl:when test="contains($string,'&#10;')">
                    <br />
                    <xsl:copy-of select="pdxf:nl2br(substring-after($string,'&#10;'))" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$string"/>
                </xsl:otherwise>
            </xsl:choose>
            </xsl:variable>
            <func:result><xsl:copy-of select="$format" /></func:result>
    </func:function>

</xsl:stylesheet>