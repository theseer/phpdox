<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"
    xmlns:file="http://xml.phpdox.de/src#" exclude-result-prefixes="#default file">
    
    <xsl:output method="xml" indent="yes" encoding="utf-8" />
    <xsl:param name="mode" select="'detail'" />
    <xsl:param name="extension" select="'xhtml'" />
    
    <xsl:template match="/">        
        <ul class="unstyled">
        <xsl:choose>
            <xsl:when test="//file:namespace">
                <xsl:apply-templates select="//file:namespace">
                    <xsl:sort select="@name" order="ascending" />
                </xsl:apply-templates>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="//file:class|//file:interface">
                    <xsl:sort select="@name" order="ascending" />
                </xsl:apply-templates>
            </xsl:otherwise>
        </xsl:choose>
        </ul>
    </xsl:template>
    
    <xsl:template match="file:namespace">
        <li>
            <h3><xsl:value-of select="@name" /></h3>
            <p>
                <xsl:if test="file:class|file:interface">
                    <ul>
                        <xsl:apply-templates select="file:class|file:interface">
                            <xsl:sort select="@name" order="ascending" />
                        </xsl:apply-templates>
                    </ul>
                </xsl:if>
            </p>
            <!-- 
            <p>
                <a class="btn" href="#">View
                    details
                </a>
            </p>-->
        </li>
    </xsl:template>
    
    <xsl:template match="file:class|file:interface|file:trait">
        <li>
            <xsl:variable name="link">
                <xsl:if test="$mode = 'detail'">../</xsl:if> 
                <xsl:choose>
                    <xsl:when test="local-name(.) = 'class'">classes</xsl:when>
                    <xsl:when test="local-name(.) = 'interface'">interfaces</xsl:when>
                    <xsl:otherwise>traits</xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <a href="{$link}/{translate(@full, '\', '_')}.{$extension}">
                <xsl:value-of select="@name" />
            </a>
        </li>
    </xsl:template>
</xsl:stylesheet>