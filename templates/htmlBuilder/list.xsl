<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"
    xmlns:file="http://xml.phpdox.de/src#" exclude-result-prefixes="#default file">
    
    <xsl:output method="xml" indent="yes" encoding="utf-8" />
    <xsl:param name="class" />
    
    <xsl:template match="/">        
        <ul class="classlist">
        <xsl:choose>
            <xsl:when test="//file:namespace">
                <xsl:apply-templates select="//file:namespace" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="//file:class|//file:interface" />
            </xsl:otherwise>
        </xsl:choose>
        </ul>
    </xsl:template>
    
    <xsl:template match="file:namespace">
        <li>
            <span class="namespace"><xsl:value-of select="@name" /></span>            
            <xsl:if test="file:class|file:interface">
                <ul id="namespace{position()}">
                    <xsl:apply-templates select="file:class|//file:interface" />
                </ul>
            </xsl:if>
        </li>
    </xsl:template>
    
    <xsl:template match="file:class|file:interface">
        <li>
            <a href="{translate(@full, '\', '_')}.xhtml">
                <xsl:value-of select="@name" />
            </a>
        </li>
    </xsl:template>
</xsl:stylesheet>