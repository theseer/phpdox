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
        <html class="no-js" lang="en">
            <head>
                <meta charset="utf-8" />
                <title><xsl:value-of select="$project/@name" /> - <xsl:value-of select="$classname" /> - API Documentation</title>
                <link href="../css/normalize.css" rel="stylesheet" type="text/css" media="all" />
                <link href="../css/styles.css" rel="stylesheet" type="text/css" media="all" />
                <!-- next comment for IE-performance -->
                <!--[if lte IE 8]><![endif]-->
                <!--[if lte IE 8]><link href="../css/oldie.css" rel="stylesheet" type="text/css" media="all" /><![endif]-->
            </head>
            <body>

                <div class="wrapper clearfix">
                    <div class="topbar clearfix">
                        <h1><a class="brand" href="../index.html"><xsl:value-of select="$project/@name" /> - API Documentation</a></h1>
                        <ul class="nav">
                            <li class="active"><a href="../index.html">Overview</a></li>
                        </ul>
                    </div>

                    <xsl:call-template name="sidebar" />

                    <div class="content">
                        <h2><span style="font-size:60%"><xsl:value-of select="$class/@namespace" />\</span><xsl:value-of select="$class/@name" /></h2>

                        <xsl:for-each select="$class/src:docblock/src:description">
                            <div class="file-notice">
                                <p><xsl:value-of select="@compact" /></p>
                                <xsl:if test="text() != ''">
                                    <p><pre><xsl:value-of select="." /></pre></p>
                                </xsl:if>
                            </div>
                        </xsl:for-each>

                        <ul class="fileinfos">
                            <xsl:apply-templates select="$class/src:docblock/*[local-name()!='description']">
                                <xsl:sort select="local-name()" order="ascending" />
                            </xsl:apply-templates>
                        </ul>

                        <xsl:variable name="inheritance" select="phe:getInheritanceInfo($class)" />
                        <xsl:if test="count($inheritance/src:of//src:class) &gt; 1">
                            <h3>Inheritance</h3>
                            <ul class="inheritance">
                                <xsl:for-each select="$inheritance/src:of//src:class">
                                    <xsl:call-template name="inherit">
                                        <xsl:with-param name="ctx" select="." />
                                    </xsl:call-template>
                                </xsl:for-each>
                            </ul>
                        </xsl:if>
                        <xsl:if test="$inheritance/src:by/src:class">
                            <h3>Extended by</h3>
                            <ul>
                                <xsl:for-each select="$inheritance/src:by/src:class">
                                    <li><xsl:copy-of select="phe:classLink(.)" /></li>
                                </xsl:for-each>
                            </ul>
                        </xsl:if>

                        <xsl:if test="$class/src:implements">
                            <h3>Implements</h3>
                            <ul class="varlist">
                            <xsl:for-each select="$class/src:implements">
                                <li><xsl:copy-of select="phe:classLink(.)" /></li>
                            </xsl:for-each>
                            </ul>
                        </xsl:if>

                        <xsl:if test="$class/src:constant">
                            <h3>Constants</h3>
                            <ul class="varlist">
                                <xsl:apply-templates select="$class/src:constant" />
                            </ul>
                        </xsl:if>

                        <xsl:if test="$class/src:member">
                            <h3>Members</h3>
                            <ul class="varlist">
                                <xsl:apply-templates select="$class/src:member" />
                            </ul>
                        </xsl:if>

                        <xsl:if test="$class/src:constructor|$class/src:destroctur|$class/src:method">
                            <h3>Methods</h3>
                            <ul class="varlist">
                                <xsl:apply-templates select="$class/src:constructor|$class/src:destructor" />
                                <xsl:apply-templates select="$class/src:method">
                                    <xsl:sort select="@visibility" order="descending" />
                                    <xsl:sort select="@name" />
                                </xsl:apply-templates>
                            </ul>
                        </xsl:if>


                    </div>
                </div>
            </body>
        </html>
    </xsl:template>

    <xsl:template name="inherit">
        <xsl:param name="ctx" />
        <li>
            <xsl:choose>
                <xsl:when test="$ctx/@full != $class/@full">
                    <xsl:copy-of select="phe:classLink($ctx)" />
                </xsl:when>
                <xsl:otherwise><strong><xsl:value-of select="$ctx/@full" /></strong></xsl:otherwise>
            </xsl:choose>
        </li>        
    </xsl:template>

    <xsl:template name="sidebar">
        <div class="navigation">
            <xsl:if test="$class/src:constant">
                <h3>Constants</h3>
                <ul>
                    <xsl:for-each select="$class/src:constant">
                        <li><a href="#{@name}"><xsl:value-of select="@name" /></a></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="$class/src:member">
                <h3>Members</h3>
                <ul>
                    <xsl:for-each select="$class/src:member">
                        <li><a href="#{@name}"><xsl:value-of select="@name" /></a></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="$class/src:method|$class/src:constructor|$class/src:destructor">
                <h3>Methods</h3>
                <ul>
                    <xsl:for-each select="$class/src:method|$class/src:constructor|$class/src:destructor">
                        <xsl:sort select="@name" order="ascending" />
                        <li><a href="#{@name}"><xsl:value-of select="@name" /></a></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
        </div>
    </xsl:template>
    
    <!--  ## DOCBLOCK NODES ## -->
    
    <xsl:template match="src:description">
        <li>
            <xsl:value-of select="@compact" />
            <xsl:if test="text() != ''">
                <pre><xsl:value-of select="." /></pre>
            </xsl:if>
        </li>
    </xsl:template>    

    <xsl:template match="src:author">
        <li>
            <b>Author: </b> <xsl:value-of select="@value" />
        </li>
    </xsl:template>

    <xsl:template match="src:copyright">
        <li>
            <b>Copyright: </b> <xsl:value-of select="@value" />
        </li>
    </xsl:template>

    <xsl:template match="src:license">
        <li>
            <b>License: </b> <xsl:value-of select="@name" />
        </li>
    </xsl:template>

    <xsl:template match="src:var">    
        <p><em><xsl:value-of select="@type" /></em></p>
    </xsl:template>

    <!--  ## CONSTANTS ## -->
    <xsl:template match="src:constant">
        <li>
            <a name="{@name}" />
            <xsl:value-of select="@name" /> = <xsl:value-of select="@value" />
            <xsl:for-each select="src:docblock">
                <em>&#160;<xsl:value-of select="src:var/@type" /></em>
                <p>
                    <xsl:apply-templates select="src:description" />
                </p>                    
            </xsl:for-each>
            <hr />
        </li>
    </xsl:template>    
    
    <!--  ## MEMBERS ## -->
    <xsl:template match="src:member">
        <li>
            <a name="{@name}" />
            <h4>
                <xsl:call-template name="modifiers">
                    <xsl:with-param name="ctx" select="." />
                </xsl:call-template>
                <xsl:if test="src:docblock/src:var">
                    <xsl:value-of select="src:docblock/src:var/@type" />&#160;
                </xsl:if>
                $<xsl:value-of select="@name" />
            </h4>
            <xsl:if test="src:docblock/*">
                <ul class="varlist">
                    <xsl:apply-templates select="src:docblock/*[local-name() != 'var']" />
                </ul>
            </xsl:if>
            <!--
            <xsl:for-each select="src:docblock">
                <em>&#160;<xsl:value-of select="src:var/@type" /></em>
            </xsl:for-each>
            <xsl:if test="src:default">
                <p><b>Default:</b>&#160;<code><xsl:value-of select="src:default" /></code></p>
            </xsl:if>
            -->
        </li>
    </xsl:template>    
    
    <!--  ## METHODS ## -->
    <xsl:template match="src:method|src:constructor|src:destructor">
        <li>
            <a name="{@name}" />
            <h4>
                <xsl:call-template name="modifiers">
                    <xsl:with-param name="ctx" select="." />
                </xsl:call-template>
                <xsl:value-of select="@name" /><span style="font-size:90%;">( <xsl:apply-templates select="src:parameter[1]" /> )</span>
            </h4>
            <xsl:for-each select="src:docblock">
                <p style="font-size:110%; padding-top:5px;">
                    <xsl:apply-templates select="src:description" />
                </p>
            </xsl:for-each>
        </li>
    </xsl:template>    
    
    <xsl:template match="src:parameter">
        <xsl:if test="@optional = 'true'">[</xsl:if>
        <xsl:choose>
            <xsl:when test="@type='object'">
                <em><xsl:copy-of select="phe:classLink(.)" /></em>&#160;
            </xsl:when>
            <xsl:when test="@type='array'">
                <em>Array</em>&#160;
            </xsl:when>
            <xsl:when test="@type='{unknown}'">
                <xsl:variable name="name" select="@name" />
                <xsl:for-each select="src:docblock/src:param[@name=$name]">
                    <em><xsl:copy-of select="phe:classLink(.)" /></em>&#160;
                </xsl:for-each>            
            </xsl:when>            
        </xsl:choose>
        <strong>
            <xsl:if test="@byreference = 'true'">&amp;</xsl:if>$<xsl:value-of select="@name" />
        </strong>
        <xsl:if test="src:default"><small> = <xsl:value-of select="src:default" /></small></xsl:if>
        <xsl:if test="following-sibling::src:parameter">, <xsl:apply-templates select="following-sibling::src:parameter[1]" /></xsl:if>
        <xsl:if test="@optional = 'true'">&#160;]</xsl:if>
    </xsl:template>

    <!--  ## shared ## -->
    
    <xsl:template name="modifiers">
        <xsl:param name="ctx" />
        <xsl:for-each select="$ctx/@visibility|$ctx/@static|$ctx/@final|$ctx/@abstract">
            <xsl:if test=". != 'false'">
                <span class="label {.}"><xsl:value-of select="." /></span>
            </xsl:if>
        </xsl:for-each>

    </xsl:template>
</xsl:stylesheet>