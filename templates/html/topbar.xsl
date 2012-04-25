<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">

    <xsl:param name="extension" select="'xhtml'" />

    <xsl:template name="topbar">
        <xsl:param name="rel" select="'.'" />
        <xsl:param name="active" select="''" />    

                <div class="topbar">
                    <div class="fill">
                        <div class="container">
                            <a class="brand" href="{$rel}/index.{$extension}"><xsl:value-of select="$project/@name" /> - API Documentation</a>
                            <ul class="nav">
                                <li>
                                    <xsl:if test="$active = 'index'">
                                        <xsl:attribute name="class">active</xsl:attribute>
                                    </xsl:if>
                                    <a href="{$rel}/index.{$extension}">Overview</a>
                                </li>
                                <!-- 
                                <li>
                                    <xsl:if test="$active = 'classes'">
                                        <xsl:attribute name="class">active</xsl:attribute>
                                    </xsl:if>
                                    <a href="{$rel}/classes.{$extension}">Classes</a>
                                </li>
                                <li>
                                    <xsl:if test="$active = 'interfaces'">
                                        <xsl:attribute name="class">active</xsl:attribute>
                                    </xsl:if>
                                    <a href="{$rel}/interfaces.{$extension}">Interfaces</a>
                                </li>
                                <li>
                                    <xsl:if test="$active = 'traits'">
                                        <xsl:attribute name="class">active</xsl:attribute>
                                    </xsl:if>
                                    <a href="{$rel}/traits.{$extension}">Traits</a>
                                </li>
                                -->
                            </ul>                            
                        </div>
                    </div>
                </div>

    </xsl:template>
    
</xsl:stylesheet>