<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:pdx="http://xml.phpdox.net/src#"
                exclude-result-prefixes="pdx">

    <xsl:import href="components.xsl" />
    <xsl:import href="functions.xsl" />
    <xsl:import href="synopsis.xsl" />

    <xsl:output method="xml" indent="yes" encoding="UTF-8" doctype-system="about:legacy-compat" />

    <xsl:variable name="unit" select="/*[1]" />

    <xsl:param name="type" select="'classes'" />
    <xsl:param name="title" select="'Classes'" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head">
                <xsl:with-param name="title" select="$unit/@full" />
            </xsl:call-template>
            <body>
                <xsl:call-template name="nav" />
                <div id="mainstage">
                    <xsl:call-template name="breadcrumb" />
                    <xsl:call-template name="sidenav" />
                    <section>
                        <a name="introduction" />
                        <h1><small><xsl:value-of select="$unit/@namespace" />\</small><xsl:value-of select="$unit/@name" /></h1>
                        <h4><xsl:value-of select="$unit/pdx:docblock/pdx:description/@compact" /></h4>
                        <p><xsl:value-of select="$unit/pdx:docblock/pdx:description" /></p>
                        <xsl:if test="$unit/pdx:docblock">
                        <xsl:call-template name="docblock" />
                        </xsl:if>

                        <a name="synopsis" />
                        <h2>Synopsis</h2>
                        <xsl:call-template name="synopsis">
                            <xsl:with-param name="unit" select="$unit" />
                        </xsl:call-template>

                        <xsl:if test="$unit/pdx:extends|$unit/pdx:extender|$unit/pdx:implements|$unit/pdx:uses">
                        <a name="hierarchy" />
                        <h2>Hierarchy</h2>
                        <xsl:call-template name="hierarchy" />
                        </xsl:if>

                        <xsl:if test="$unit//pdx:enrichment[@type = 'phpunit']">
                        <a name="coverage" />
                        <h2>Coverage</h2>
                        <xsl:call-template name="coverage" />
                        </xsl:if>

                        <xsl:call-template name="violations">
                            <xsl:with-param name="ctx" select="$unit//pdx:enrichments" />
                        </xsl:call-template>

                        <xsl:if test="//pdx:todo">
                        <a name="tasks" />
                         <h2>Tasks</h2>
                        <xsl:call-template name="tasks" />
                        </xsl:if>

                        <xsl:if test="//pdx:constant">
                        <a name="constants" />
                        <h2>Constants</h2>
                        <xsl:call-template name="constants" />
                        </xsl:if>

                        <xsl:if test="//pdx:member">
                        <a name="members" />
                        <h2>Members</h2>
                        <xsl:call-template name="members" />
                        </xsl:if>

                        <xsl:if test="//pdx:method">
                        <a name="methods" />
                        <h2>Methods</h2>
                        <xsl:call-template name="methods" />
                        </xsl:if>

                        <xsl:if test="//pdx:enrichment[@type = 'git']">
                            <a name="history" />
                            <h2>History</h2>
                            <xsl:call-template name="git-history" />
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
                <xsl:if test="$unit/@namespace != ''">
                    <li class="separator"><a href="{$base}{$type}.{$extension}#{translate($unit/@namespace, '\', '_')}"><xsl:value-of select="$unit/@namespace" /></a></li>
                </xsl:if>
                <li class="separator"><xsl:value-of select="$unit/@name" /></li>
            </ul>
        </div>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="sidenav">
        <nav class="box">
            <ul>
                <li><a href="#introduction">Introduction</a></li>
                <li><a href="#synopsis">Synopsis</a></li>
                <xsl:if test="$unit/pdx:extends|$unit/pdx:extender|$unit/pdx:implements|$unit/pdx:uses">
                <li><a href="#hierarchy">Hierarchy</a></li>
                </xsl:if>
                <xsl:if test="$unit//pdx:enrichment[@type = 'phpunit']">
                <li><a href="#coverage">Coverage</a></li>
                </xsl:if>
                <li><a href="#violations">Violations</a></li>
                <xsl:if test="//pdx:todo">
                <li><a href="#tasks">Tasks</a></li>
                </xsl:if>
                <xsl:if test="//pdx:constant">
                <li><a href="#constants">Constants</a></li>
                </xsl:if>
                <xsl:if test="//pdx:member">
                <li><a href="#members">Members</a></li>
                </xsl:if>
                <xsl:if test="//pdx:method">
                <li><a href="#methods">Methods</a></li>
                </xsl:if>
                <xsl:if test="//pdx:enrichment[@type = 'git']">
                <li><a href="#history">History</a></li>
                </xsl:if>
            </ul>
        </nav>
    </xsl:template>


</xsl:stylesheet>