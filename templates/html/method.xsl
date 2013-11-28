<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:pdx="http://xml.phpdox.net/src#"
                xmlns:pdxf="http://xml.phpdox.net/functions"
                exclude-result-prefixes="pdx pdxf">

    <xsl:import href="components.xsl" />
    <xsl:import href="functions.xsl" />

    <xsl:variable name="unit" select="/*[1]" />

    <xsl:param name="methodName" select="'undefined'" />
    <xsl:param name="type" select="'classes'" />
    <xsl:param name="title" select="'Classes'" />

    <xsl:variable name="method" select="$unit/pdx:*[@name = $methodName]" />

    <xsl:output method="xml" indent="yes" encoding="UTF-8" doctype-system="about:legacy-compat" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head">
                <xsl:with-param name="title" select="'Test Method'" />
            </xsl:call-template>
            <body>
                <xsl:call-template name="nav" />
                <div id="mainstage">
                    <xsl:call-template name="breadcrumb" />
                    <xsl:call-template name="sidenav" />
                    <section>
                        <h1><small><xsl:value-of select="$unit/@full" />::</small><xsl:value-of select="$methodName" /></h1>
                        <h4><xsl:value-of select="$method/pdx:docblock/pdx:description/@compact" /></h4>
                        <p><xsl:value-of select="$method/pdx:docblock/pdx:description" /></p>
                        <xsl:if test="$method/pdx:docblock">
                            <xsl:call-template name="docblock">
                                <xsl:with-param name="ctx" select="$method" />
                            </xsl:call-template>
                        </xsl:if>

                        <xsl:call-template name="signature" />

                        <xsl:if test="$method/pdx:parameter">
                            <xsl:call-template name="parameterlist" />
                        </xsl:if>

                        <xsl:if test="$method/pdx:docblock/pdx:return">
                            <xsl:call-template name="return">
                                <xsl:with-param name="return" select="$method/pdx:docblock/pdx:return" />
                            </xsl:call-template>
                        </xsl:if>

                        <xsl:if test="$method/pdx:docblock/pdx:throws">
                            <xsl:call-template name="throws" />
                        </xsl:if>

                        <xsl:if test="$unit//pdx:interface[pdx:method/@name = $methodName]">
                            <xsl:call-template name="interface" />
                        </xsl:if>

                        <xsl:if test="$unit//pdx:parent[pdx:method/@name = $methodName]">
                            <xsl:call-template name="overrides" />
                        </xsl:if>

                        <xsl:if test="$method//pdx:enrichment[@type = 'phpunit']">
                            <xsl:call-template name="coverage">
                                <xsl:with-param name="ctx" select="$method" />
                            </xsl:call-template>
                            <xsl:call-template name="tests" />
                        </xsl:if>

                        <xsl:call-template name="violations">
                            <xsl:with-param name="ctx" select="$method//pdx:enrichments" />
                        </xsl:call-template>

                        <xsl:if test="$method//pdx:todo">
                            <xsl:call-template name="tasks">
                                <xsl:with-param name="ctx" select="$method" />
                            </xsl:call-template>
                        </xsl:if>

                    </section>
                </div>

                <xsl:call-template name="footer" />
            </body>
        </html>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="breadcrumb">
        <div class="box">
            <ul class="breadcrumb">
                <li><a href="{$base}index.{$extension}">Overview</a></li>
                <li class="separator"><a href="{$base}{$type}.{$extension}"><xsl:value-of select="$title" /></a></li>
                <li class="separator"><a href="{$base}{$type}.{$extension}#{translate($unit/@namespace, '\', '_')}"><xsl:value-of select="$unit/@namespace" /></a></li>
                <li class="separator"><xsl:copy-of select="pdxf:link($unit, '', $unit/@name)" /></li>
                <li class="separator"><xsl:value-of select="$method/@name" /></li>
            </ul>
        </div>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="sidenav">
        <nav class="box">
            <ul>
                <li><a href="#introduction">Introduction</a></li>
                <li><a href="#synopsis">Synopsis</a></li>
            </ul>
        </nav>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="parameterlist">
        <h2>Parameters</h2>
        <dl class="styled">
            <xsl:for-each select="$method/pdx:parameter">
                <xsl:variable name="param" select="." />
                <dt><code>$<xsl:value-of select="@name" /></code></dt>
                <dd><xsl:value-of select="$method/pdx:docblock/pdx:param[@variable = concat('$', $param/@name)]/@description" /></dd>
            </xsl:for-each>
        </dl>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="signature">
        <h2>Signature</h2>
        <div class="styled synopsis">
            <code>
                <xsl:value-of select="$method/@visibility" /> function <xsl:value-of select="$methodName" />(<xsl:if test="$method/pdx:parameter">
                <xsl:call-template name="parameter">
                    <xsl:with-param name="param" select="$method/pdx:parameter[1]" />
                </xsl:call-template>&#160;</xsl:if>)
            </code>
        </div>
    </xsl:template>

    <xsl:template name="parameter">
        <xsl:param name="param" />
        <xsl:if test="$param/@default">[</xsl:if>
        <xsl:choose>
            <xsl:when test="$param/@type = 'object'">
                <xsl:copy-of select="pdxf:link($param/pdx:type, '', $param/pdx:type/@name)" />
            </xsl:when>
            <xsl:when test="$param/@type = '{unknown}'">
                <xsl:if test="$method/pdx:docblock/pdx:param[@variable = concat('$', $param/@name)]">
                    <xsl:variable name="dparam" select="$method/pdx:docblock/pdx:param[@variable = concat('$', $param/@name)]" />
                    <xsl:choose>
                        <xsl:when test="$dparam/@type = 'object'">
                            <xsl:copy-of select="pdxf:link($dparam/pdx:type, '', $dparam/pdx:type/@name)" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$dparam/@type" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:if>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$param/@type" />
            </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="$param/@byreference = 'true'">&amp;</xsl:if>
        $<xsl:value-of select="$param/@name" />
        <xsl:if test="$param/@default"> = <xsl:value-of select="$param/@default" /></xsl:if>
        <xsl:if test="$param/following-sibling::pdx:parameter">,
            <xsl:call-template name="parameter">
                <xsl:with-param name="param" select="$param/following-sibling::pdx:parameter" />
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="$param/@default">]</xsl:if>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="return">
        <xsl:param name="return" />
        <h2>Returns</h2>
        <dl class="styled">
            <dt><xsl:call-template name="type">
                <xsl:with-param name="ctx" select="$method" />
            </xsl:call-template></dt>
            <dd><xsl:value-of select="$return/@description" />
                <xsl:if test="$return/text() != ''">
                    <br/><xsl:value-of select="$return/text()" />
                </xsl:if></dd>
        </dl>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="throws">
        <h2>Errors/Exceptions</h2>
        <dl class="styled">
            <xsl:for-each select="$method/pdx:docblock/pdx:throws">
                <dt><code><xsl:copy-of select="pdxf:link(pdx:type, '', pdx:type/@name)" /></code></dt>
                <dd><xsl:value-of select="@description" /></dd>
            </xsl:for-each>
        </dl>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="interface">
        <h2>Defined by Interface</h2>
        <p class="styled">
            <code><xsl:copy-of select="pdxf:link($unit/pdx:interface[pdx:method/@name = $methodName])" /></code>
        </p>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="overrides">
        <h2>Parent Implementation<xsl:if test="count($unit//pdx:parent[pdx:method/@name = $methodName]) &gt; 1">s</xsl:if></h2>
        <ul class="styled">
            <xsl:for-each select="$unit//pdx:parent[pdx:method/@name = $methodName]">
                <li><code><xsl:copy-of select="pdxf:link(., $methodName)" /></code></li>
            </xsl:for-each>
        </ul>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="tests">
        <h2>Tests</h2>
        <ul class="styled">
            <li>[ X ] — \Some\Test\Class::ThisIsAFancyTwstMethodName</li>
            <li>[ X ] — \Some\Test\Class::ThisIsAFancyTwstMethodName</li>
            <li>[ X ] — \Some\Test\Class::ThisIsAFancyTwstMethodName</li>
            <li>[ X ] — \Some\Test\Class::ThisIsAFancyTwstMethodName</li>
            <li>[ X ] — \Some\Test\Class::ThisIsAFancyTwstMethodName</li>
        </ul>
    </xsl:template>

</xsl:stylesheet>