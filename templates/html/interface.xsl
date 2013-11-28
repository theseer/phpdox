<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:phpdox="http://xml.phpdox.net/src#"
                xmlns:idx="http://xml.phpdox.net/src#"
                exclude-result-prefixes="idx phpdox">

    <xsl:import href="components.xsl" />
    <xsl:import href="functions.xsl" />
    <xsl:import href="synopsis.xsl" />

    <xsl:output method="xml" indent="yes" encoding="UTF-8" doctype-system="about:legacy-compat" />

    <xsl:variable name="unit" select="/*[1]" />

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
                        <h4><xsl:value-of select="$unit/phpdox:docblock/phpdox:description/@compact" /></h4>
                        <p><xsl:value-of select="$unit/phpdox:docblock/phpdox:description" /></p>
                        <xsl:if test="$unit/phpdox:docblock">
                        <xsl:call-template name="docblock" />
                        </xsl:if>

                        <a name="synopsis" />
                        <h2>Synopsis</h2>
                        <xsl:call-template name="synopsis">
                            <xsl:with-param name="unit" select="$unit" />
                        </xsl:call-template>

                        <xsl:if test="$unit/phpdox:extends|$unit/phpdox:extender|$unit/phpdox:implements|$unit/phpdox:uses">
                        <a name="hierarchy" />
                        <h2>Hierarchy</h2>
                        <xsl:call-template name="hierarchy">
                            <xsl:with-param name="dir" select="'interfaces'" />
                        </xsl:call-template>
                        </xsl:if>

                        <xsl:call-template name="violations">
                            <xsl:with-param name="ctx" select="$unit//phpdox:enrichments" />
                        </xsl:call-template>

                        <xsl:if test="//phpdox:todo">
                        <a name="tasks" />
                        <h2>Tasks</h2>
                        <xsl:call-template name="tasks" />
                        </xsl:if>

                        <xsl:if test="//phpdox:constant">
                        <a name="constants" />
                        <h2>Constants</h2>
                        <xsl:call-template name="constants" />
                        </xsl:if>

                        <xsl:if test="//phpdox:method">
                        <a name="methods" />
                        <h2>Methods</h2>
                        <xsl:call-template name="methods" />
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
                <li class="separator"><a href="{$base}interfaces.{$extension}">Interfaces</a></li>
                <li class="separator"><a href="{$base}interfaces.{$extension}#{translate($unit/@namespace, '\', '_')}"><xsl:value-of select="$unit/@namespace" /></a></li>
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
                <xsl:if test="$unit/phpdox:extends|$unit/phpdox:extender|$unit/phpdox:implements|$unit/phpdox:uses">
                <li><a href="#hierarchy">Hierarchy</a></li>
                </xsl:if>
                <li><a href="#violations">Violations</a></li>
                <xsl:if test="//phpdox:todo">
                <li><a href="#tasks">Tasks</a></li>
                </xsl:if>
                <xsl:if test="//phpdox:constant">
                <li><a href="#constants">Constants</a></li>
                </xsl:if>
                <xsl:if test="//phpdox:method">
                <li><a href="#methods">Methods</a></li>
                </xsl:if>
            </ul>
        </nav>
    </xsl:template>


</xsl:stylesheet>