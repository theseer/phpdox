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

    <func:function name="pdxf:link">
        <xsl:param name="ctx"/>
        <xsl:param name="method"/>
        <xsl:param name="copy"/>

        <xsl:variable name="dir">
            <xsl:choose>
                <xsl:when test="local-name($ctx) = 'implements'">interfaces</xsl:when>
                <xsl:when test="local-name($ctx) = 'uses'">traits</xsl:when>
                <xsl:when test="local-name($ctx) = 'interface'">interfaces</xsl:when>

                <xsl:when test="local-name($ctx) = 'interface'">interfaces</xsl:when>
                <xsl:when test="local-name($ctx) = 'trait'">traits</xsl:when>
                <xsl:when test="local-name($ctx) = 'class'">classes</xsl:when>

                <xsl:when test="local-name($unit) = 'interface'">interfaces</xsl:when>
                <xsl:when test="local-name($unit) = 'class'">classes</xsl:when>
                <xsl:when test="local-name($unit) = 'trait'">traits</xsl:when>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="withMethod">
            <xsl:if test="$method != ''">
                <xsl:value-of select="concat('/', $method)"/>
            </xsl:if>
        </xsl:variable>

        <xsl:variable name="link">
            <xsl:value-of
                    select="concat($base, $dir, '/', translate($ctx/@full, '\', '_'), $withMethod, '.', $extension)"/>
        </xsl:variable>

        <xsl:variable name="text">
            <xsl:choose>
                <xsl:when test="$copy">
                    <xsl:value-of select="$copy"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$ctx/@name"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <func:result>
            <xsl:choose>
                <xsl:when test="$ctx/@unresolved = 'true'">
                    <xsl:value-of select="$text"/>
                </xsl:when>
                <xsl:otherwise>
                    <a title="{$ctx/@full}" href="{$link}">
                        <xsl:value-of select="$text"/>
                    </a>
                </xsl:otherwise>
            </xsl:choose>
        </func:result>
    </func:function>

    <func:function name="pdxf:nl2br">
        <xsl:param name="string"/>
        <xsl:variable name="format">
            <xsl:value-of select="normalize-space(substring-before($string,'&#10;'))"/>
            <xsl:choose>
                <xsl:when test="contains($string,'&#10;')">
                    <br/>
                    <xsl:copy-of select="pdxf:nl2br(substring-after($string,'&#10;'))"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$string"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <func:result>
            <xsl:copy-of select="$format"/>
        </func:result>
    </func:function>

    <func:function name="pdxf:format-number">
        <xsl:param name="value"/>
        <xsl:param name="format">0.##</xsl:param>
        <func:result>
            <xsl:choose>
                <xsl:when test="string(number($value))='NaN'">
                    <xsl:value-of select="format-number(0, $format)"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="format-number($value, $format)"/>
                </xsl:otherwise>
            </xsl:choose>
        </func:result>
    </func:function>

    <func:function name="pdxf:filesize">
        <xsl:param name="bytes"/>

        <func:result>
            <xsl:choose>
                <xsl:when test="floor($bytes div 1024) = 0">
                    <xsl:value-of select="$bytes"/> Bytes
                </xsl:when>

                <xsl:when test="floor($bytes div 1048576) = 0">
                    <xsl:value-of select="format-number(($bytes div 1024), '0.0')"/> KB
                </xsl:when>

                <xsl:otherwise>
                    <xsl:value-of select="format-number(($bytes div 1048576), '0.00')"/> MB
                </xsl:otherwise>

            </xsl:choose>
        </func:result>

    </func:function>

</xsl:stylesheet>
