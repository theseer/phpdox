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
                <xsl:with-param name="title" select="concat($unit/@full, '::', $methodName)" />
            </xsl:call-template>
            <body>
                <xsl:call-template name="nav" />
                <div id="mainstage">
                    <xsl:call-template name="breadcrumb" />
                    <xsl:call-template name="sidenav" />
                    <section>
                        <h1><small><xsl:value-of select="$unit/@full" />::</small><xsl:value-of select="$methodName" /></h1>
                        <h4><xsl:value-of select="$method/pdx:docblock//pdx:description/@compact" /></h4>
                        <p><xsl:value-of select="$method/pdx:docblock//pdx:description" /></p>
                        <xsl:if test="$method/pdx:docblock">
                            <xsl:call-template name="docblock">
                                <xsl:with-param name="ctx" select="$method" />
                            </xsl:call-template>
                        </xsl:if>

                        <xsl:call-template name="signature" />

                        <xsl:if test="$method/pdx:parameter">
                            <xsl:call-template name="parameterlist" />
                        </xsl:if>

                        <xsl:if test="$method/pdx:docblock//pdx:return">
                            <xsl:call-template name="return">
                                <xsl:with-param name="return" select="$method/pdx:docblock//pdx:return" />
                            </xsl:call-template>
                        </xsl:if>

                        <xsl:if test="$method/pdx:docblock//pdx:throws">
                            <xsl:call-template name="throws" />
                        </xsl:if>

                        <xsl:if test="$unit//pdx:interface[pdx:method/@name = $methodName]">
                            <xsl:call-template name="interface" />
                        </xsl:if>

                        <xsl:if test="$unit//pdx:parent[pdx:method/@name = $methodName]">
                            <xsl:call-template name="overrides" />
                        </xsl:if>

                        <xsl:if test="$method//pdx:enrichment[@type = 'phpunit']">
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
                <xsl:if test="$method/pdx:parameter">
                    <li><a href="#parameter">Parameter</a></li>
                </xsl:if>

                <xsl:if test="$method/pdx:docblock//pdx:return">
                    <li><a href="#return">Return</a></li>
                </xsl:if>

                <xsl:if test="$method/pdx:docblock//pdx:throws">
                    <li><a href="#throws">Throws</a></li>
                </xsl:if>

                <xsl:if test="$unit//pdx:interface[pdx:method/@name = $methodName]">
                    <li><a href="#interface">Interface</a></li>
                </xsl:if>

                <xsl:if test="$unit//pdx:parent[pdx:method/@name = $methodName]">
                    <li><a href="#overrides">Overrides</a></li>
                </xsl:if>

                <xsl:if test="$method//pdx:enrichment[@type = 'phpunit']">
                    <li><a href="#tests">Tests</a></li>
                </xsl:if>

                <xsl:if test="$method//pdx:enrichtment[@type = 'checkstyle' or @type='pmd']">
                    <li><a href="#violations">Violations</a></li>
                </xsl:if>

                <xsl:if test="$method//pdx:todo">
                    <li><a href="#tasks">Tasks</a></li>
                </xsl:if>
                <xsl:if test="$unit/@start"><!-- hack: test for start line == we know something about this class -->
                    <li><a href="{$base}source/{$unit/pdx:file/@relative}.{$extension}#line{$method/@start}">Source</a></li>
                </xsl:if>

            </ul>
        </nav>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="parameterlist">
        <h2 id="parameterlist">Parameters</h2>
        <dl class="styled">
            <xsl:for-each select="$method/pdx:parameter">
                <xsl:variable name="param" select="." />
                <xsl:variable name="docparam" select="$method/pdx:docblock//pdx:param[@variable = concat('$', $param/@name)]" />
                <dt><code>$<xsl:value-of select="@name" /></code>
                —
                <xsl:choose>
                    <xsl:when test="$param/@type = 'object'">
                        <xsl:copy-of select="pdxf:link($docparam/pdx:type, '', $docparam/pdx:type/@full)" />
                    </xsl:when>
                    <xsl:when test="$param/@type = '{unknown}'">
                        <xsl:value-of select="$docparam/@type" />
                    </xsl:when>
                    <xsl:otherwise><xsl:value-of select="@type" /></xsl:otherwise>
                </xsl:choose>
                </dt>
                <dd><xsl:value-of select="$docparam/@description" />
                    <xsl:if test="$docparam/text() != ''">
                        <br/><xsl:copy-of select="pdxf:nl2br($docparam)" />
                    </xsl:if>
                </dd>
            </xsl:for-each>
        </dl>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="signature">
        <h2 id="signature">Signature</h2>
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
                <xsl:if test="$method/pdx:docblock//pdx:param[@variable = concat('$', $param/@name)]">
                    <xsl:variable name="dparam" select="$method/pdx:docblock//pdx:param[@variable = concat('$', $param/@name)]" />
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
        <xsl:if test="$param/@default"> = <xsl:choose>
            <xsl:when test="$param/@default = ''"><xsl:value-of select="$param/@constant" /></xsl:when>
            <xsl:otherwise><xsl:value-of select="$param/@default" /></xsl:otherwise>
        </xsl:choose></xsl:if>
        <xsl:if test="$param/following-sibling::pdx:parameter">,
            <xsl:call-template name="parameter">
                <xsl:with-param name="param" select="($param/following-sibling::pdx:parameter)[1]" />
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="$param/@default">]</xsl:if>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="return">
        <xsl:param name="return" />
        <h2 id="return">Returns</h2>
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
        <h2 id="throws">Errors/Exceptions</h2>
        <dl class="styled">
            <xsl:for-each select="$method/pdx:docblock//pdx:throws">
                <dt><code><xsl:copy-of select="pdxf:link(pdx:type, '', pdx:type/@name)" /></code></dt>
                <dd><xsl:value-of select="@description" /></dd>
            </xsl:for-each>
        </dl>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="interface">
        <h2 id="interface">Defined by Interface</h2>
        <p class="styled">
            <code><xsl:copy-of select="pdxf:link($unit/pdx:interface[pdx:method/@name = $methodName])" /></code>
        </p>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="overrides">
        <h2 id="overrides">Parent Implementation<xsl:if test="count($unit//pdx:parent[pdx:method/@name = $methodName]) &gt; 1">s</xsl:if></h2>
        <ul class="styled">
            <xsl:for-each select="$unit//pdx:parent[pdx:method/@name = $methodName]">
                <li><code><xsl:copy-of select="pdxf:link(., $methodName)" /></code></li>
            </xsl:for-each>
        </ul>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="tests">
        <h2 id="tests">Test Coverage</h2>
        <div class="styled">
            <xsl:variable name="coverage" select="$method//pdx:enrichment[@type='phpunit']/pu:coverage" />
            <xsl:variable name="count" select="count($coverage/pu:test)" />
            <xsl:variable name="passed" select="count($coverage/pu:test[@result='0'])" />

            <h3>Information</h3>
            <ul class="styled">
                <li>Coverage: <xsl:value-of select="$coverage/@executed"/>/<xsl:value-of select="$coverage/@executable"/> Lines (<xsl:value-of select="$coverage/@coverage"/>%)</li>
                <li>Tests: <xsl:value-of select="$count" /></li>
                <li>Passed: <xsl:value-of select="$passed" /> (<xsl:choose>
                    <xsl:when test="$count = 0">0</xsl:when>
                    <xsl:otherwise><xsl:value-of select="pdxf:format-number($passed div $count * 100,'0.##')" /></xsl:otherwise>
                </xsl:choose>%)</li>
            </ul>
            <xsl:if test="$method//pdx:enrichment[@type='phpunit']/pu:coverage/pu:test">
                <h3>Tests</h3>
                <ul class="styled">
                    <xsl:for-each select="$method//pdx:enrichment[@type='phpunit']/pu:coverage/pu:test">
                        <li>[ <span class="testresult-{@status}"><xsl:value-of select="@status" /></span> ] — <xsl:value-of select="@name" /></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
        </div>
    </xsl:template>

</xsl:stylesheet>
