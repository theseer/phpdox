<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml"
    xmlns:file="http://xml.phpdox.de/src#" exclude-result-prefixes="#default file">
    
    <xsl:output method="xml" indent="yes" encoding="utf-8" />
    <xsl:param name="mode" select="'detail'" />
    <xsl:param name="extension" select="'xhtml'" />
    
    <xsl:template match="/">
        <div class="wrapper">
        <xsl:choose>
            <xsl:when test="//file:namespace">
                <xsl:apply-templates select="//file:namespace">
                    <xsl:sort select="@name" order="ascending" />
                </xsl:apply-templates>
            </xsl:when>
            <xsl:otherwise>
                <div class="linkbox">
                    <h3><xsl:call-template name="headline" /></h3>
                    <xsl:choose>
                        <xsl:when test="count(file:class|file:interface|file:trait) = 0">
                            <span style="color:#aaa">No <xsl:value-of select="local-name(/*[1])" /> defined.</span>
                        </xsl:when>
                        <xsl:otherwise>
                            <ul class="linklist">
                                <xsl:apply-templates select="//file:class|//file:interface|//file:trait">
                                    <xsl:sort select="@name" order="ascending" />
                                </xsl:apply-templates>
                            </ul>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
            </xsl:otherwise>
        </xsl:choose>
        </div>
    </xsl:template>

    <xsl:template name="headline">
        <xsl:variable name="first" select="/*[1]" />
        <xsl:choose>
            <xsl:when test="local-name($first) = 'classes'">Classes</xsl:when>
            <xsl:when test="local-name($first) = 'interfaces'">Interface</xsl:when>
            <xsl:when test="local-name($first) = 'traits'">Traits</xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="file:namespace[1]">
        <div class="linkbox">
            <h3><xsl:call-template name="headline" /></h3>
            <h4><xsl:value-of select="@name" /></h4>
            <xsl:choose>
                <xsl:when test="count(file:class|file:interface|file:trait) = 0">
                    <span>No <xsl:value-of select="local-name(/*[1])" /> defined.</span>
                </xsl:when>
                <xsl:otherwise>
                    <ul class="linklist">
                        <xsl:apply-templates select="file:class|file:interface|file:trait">
                            <xsl:sort select="@name" order="ascending" />
                        </xsl:apply-templates>
                    </ul>
                </xsl:otherwise>
            </xsl:choose>
        </div>
    </xsl:template>

    <xsl:template match="file:namespace">
        <div class="linkbox">
            <h4><xsl:value-of select="@name" /></h4>
            <ul class="linklist">
                <xsl:apply-templates select="file:class|file:interface|file:trait">
                    <xsl:sort select="@name" order="ascending" />
                </xsl:apply-templates>
            </ul>
        </div>
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
            <a href="{$link}/{translate(@full, '\', '_')}.{$extension}"><xsl:value-of select="@name" /></a>
        </li>
    </xsl:template>

</xsl:stylesheet>