<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:src="http://xml.phpdox.de/src#"
    exclude-result-prefixes="#default src">
    
    <xsl:import href="topbar.xsl" />

    <xsl:output method="xml" indent="yes" encoding="utf-8" doctype-public="html" />
    
    <xsl:param name="classname" />

    <xsl:variable name="project" select="phe:getProjectNode()"/>
    <xsl:variable name="class" select="//src:class[@full=$classname]" />

    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
            <head>
                <meta charset="UTF-8" />
                <link rel="stylesheet" href="../css/bootstrap.min.css" />
                <title><xsl:value-of select="$project/@name" /> - <xsl:value-of select="$classname" /> - API Documentation</title>
                <style type="text/css">
                    body {
                        padding-top: 60px;
                    }
                </style>                
            </head>
            <body>
                <xsl:call-template name="topbar">
                    <xsl:with-param name="rel" select="'..'" />                    
                </xsl:call-template>
                
                <div class="container-fluid">
                
                    <xsl:call-template name="sidebar" />
                    
                    <div class="content">
                        <div class="well">
                            <small><xsl:value-of select="$class/../@name" /></small><h1><xsl:value-of select="$class/@name" /></h1>
                            
                            <xsl:apply-templates select="$class/src:docblock/*">
                                <xsl:sort select="local-name()" order="ascending" />
                            </xsl:apply-templates>
                        </div>
                    
                        <xsl:if test="$class/src:constant">
                            <h2>Constants</h2>    
                            <xsl:apply-templates select="$class/src:constant" />
                        </xsl:if>
                        <xsl:if test="$class/src:member">
                            <h2>Members</h2>    
                            <xsl:apply-templates select="$class/src:member" />
                        </xsl:if>
                        <xsl:if test="$class/src:constructor|$class/src:destroctur|$class/src:method">
                            <h2 class="well">Methods</h2>
                            <div style="padding-left:1em;">
                            <ul class="unstyled">    
                                <xsl:apply-templates select="$class/src:constructor|$class/src:destructor" />
                                <xsl:apply-templates select="$class/src:method">
                                    <xsl:sort select="@visibility" order="descending" />
                                    <xsl:sort select="@name" />
                                </xsl:apply-templates>                                
                            </ul>
                            </div>
                        </xsl:if>
                        
                        <footer>
                            <p>Generated with phpDox 0.4</p>
                        </footer>
                    </div>            

                </div>
            </body>
        </html>

    </xsl:template>

    <xsl:template name="sidebar">
        <div class="sidebar">
            <div class="well">
                <xsl:if test="$class/src:constant">
                    <h5>Constants</h5>
                    <ul>
                        <xsl:for-each select="$class/src:constant">
                            <li><a href="#{@name}"><xsl:value-of select="@name" /></a></li>
                        </xsl:for-each>
                    </ul>
                </xsl:if>
                <xsl:if test="$class/src:member">
                    <h5>Members</h5>
                    <ul>
                        <xsl:for-each select="$class/src:member">
                            <li><a href="#{@name}">$<xsl:value-of select="@name" /></a></li>
                        </xsl:for-each>
                    </ul>
                </xsl:if>
                <xsl:if test="$class/src:method|$class/src:constructor|$class/src:destructor">
                    <h5>Methods</h5>
                    <ul>
                        <xsl:for-each select="$class/src:method|$class/src:constructor|$class/src:destructor">
                            <xsl:sort select="@name" order="ascending" />
                            <li><a href="#{@name}"><xsl:value-of select="@name" /></a></li>
                        </xsl:for-each>
                    </ul>
                </xsl:if>
            </div>
        </div>
    </xsl:template>
    
    <!--  ## DOCBLOCK NODES ## -->
    
    <xsl:template match="src:description">
        <p><xsl:value-of select="@compact" /></p>
        <xsl:if test="text() != ''">
            <pre><xsl:value-of select="." /></pre>
        </xsl:if>
    </xsl:template>    

    <xsl:template match="src:author">
        <p><b>Author: </b> <xsl:value-of select="@value" /></p>
    </xsl:template>

    <xsl:template match="src:copyright">
        <p><b>Copyright: </b> <xsl:value-of select="@value" /></p>
    </xsl:template>

    <xsl:template match="src:license">
        <p><b>License: </b> <xsl:value-of select="@name" /></p>
    </xsl:template>

    <!--  ## CONSTANTS ## -->
    
    <!--  ## MEMBERS ## -->
    
    <!--  ## METHODS ## -->
    <xsl:template match="src:method|src:constructor|src:destructor">
        <li>
            <a name="{@name}" />
            <h3><xsl:value-of select="@name" /></h3>
            <p style="padding-left:1em;">
                <xsl:call-template name="visibility">
                    <xsl:with-param name="modifier" select="@visibility" />
                </xsl:call-template>            
                <span style="padding-left:5px;">function <strong><xsl:value-of select="@name" /></strong>(
                    <xsl:apply-templates select="src:parameter[1]" />
                )</span>
            </p>
            <hr />
        </li>
    </xsl:template>    
    
    <xsl:template match="src:parameter">
        <xsl:if test="@optional = 'true'">[</xsl:if>
        <em><xsl:value-of select="@class" /></em>&#160;<strong>$<xsl:value-of select="@name" /></strong>
        <xsl:if test="following-sibling::src:parameter">, <xsl:apply-templates select="following-sibling::src:parameter[1]" /></xsl:if>
        <xsl:if test="@optional = 'true'">]</xsl:if>
    </xsl:template>

    <!--  ## shared ## -->
    <xsl:template name="visibility">
        <xsl:param name="modifier" />
        <span>
            <xsl:attribute name="class">label
                <xsl:choose>
                    <xsl:when test="$modifier = 'public'">success</xsl:when>
                    <xsl:when test="$modifier = 'protected'">warning</xsl:when>
                    <xsl:when test="$modifier = 'private'">important</xsl:when>
                </xsl:choose>
            </xsl:attribute>
            <xsl:value-of select="$modifier" />          
        </span>
    </xsl:template>
</xsl:stylesheet>