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
                <xsl:apply-templates select="//file:class" />
            </xsl:otherwise>
        </xsl:choose>
        </ul>
    </xsl:template>
    
    <xsl:template match="file:namespace">
        <li>
            <a href="#" onclick="phpDox.toggleNamespace('namespace{position()}')">
                <xsl:value-of select="@name" />
            </a>
            <xsl:if test="file:class">
                <ul id="namespace{position()}">
                    <xsl:apply-templates select="file:class" />
                </ul>
            </xsl:if>
        </li>
    </xsl:template>
    
    <xsl:template match="file:class">
        <li>
            <a href="#" onclick="phpDox.loadClass('{translate(@full, '\', '_')}')">
                <xsl:value-of select="@name" />
            </a>
        </li>
    </xsl:template>
</xsl:stylesheet>