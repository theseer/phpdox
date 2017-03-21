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

    <xsl:import href="functions.xsl"/>
    <xsl:import href="components.xsl" />

    <xsl:output method="xml" indent="yes" encoding="UTF-8" doctype-system="about:legacy-compat" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head" />
            <body>
                <xsl:call-template name="nav" />
                <xsl:call-template name="index" />
                <div id="mainstage">
                    <xsl:choose>
                        <xsl:when test="//pdx:enrichment[@type='phploc']">
                            <xsl:call-template name="phploc" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:call-template name="missing" />
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
                <xsl:call-template name="footer" />
            </body>
        </html>
    </xsl:template>

    <xsl:template name="index">
        <div id="index">
            <div id="projectinfo">
                <h1><xsl:value-of select="$project" /></h1>
                <h2>Software Documentation</h2>
                <p>Welcome to the Software Documentation homepage.</p>
            </div>
            <div id="buildinfo">
                <h3>Build</h3>
                <p><xsl:value-of select="//pdx:enrichment[@type='build']/pdx:date/@rfc" /></p>
                <h3>VCS Info</h3>
                <p>
                    <xsl:variable name="current" select="//pdx:enrichment[@type='git']/git:current" />
                    tag: <xsl:value-of select="$current/@describe" /><br/>
                    branch: <xsl:value-of select="$current/@branch" />
                </p>
                <h3>Used Enrichers</h3>
                <p>
                    <xsl:for-each select="//pdx:enrichment[@type='build']//pdx:enricher">
                        <xsl:sort select="@type" />
                        <xsl:value-of select="@type" /><xsl:if test="position() != last()">, </xsl:if>
                    </xsl:for-each>
                </p>
            </div>
        </div>
    </xsl:template>

    <xsl:template name="missing">
        <div class="unavailable">
            <p><strong>Warning:</strong> PHPLoc enrichment not enabled or phploc.xml not found.</p>
        </div>
    </xsl:template>

    <xsl:template name="phploc">
        <xsl:variable name="phploc" select="//pdx:enrichment[@type='phploc']" />

        <div class="column">
            <div class="container">
                <h2>Structure</h2>
                <table class="styled overview">
                    <tr>
                        <td>Namespaces</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:namespaces" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Interfaces</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:interfaces" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Traits</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:traits" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:classes" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Abstract Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:abstractClasses" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:abstractClasses div $phploc/pdx:classes * 100, '0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Concrete Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:concreteClasses" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:concreteClasses div $phploc/pdx:classes * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:methods" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Scope</td>
                        <td />
                        <td />
                    </tr>
                    <tr>
                        <td class="indent2">Non-Static Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:nonStaticMethods" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:nonStaticMethods div $phploc/pdx:methods * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent2">Static Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:staticMethods" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:staticMethods div $phploc/pdx:methods * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Visibility</td>
                        <td />
                        <td />
                    </tr>
                    <tr>
                        <td class="indent2">Public Method</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:publicMethods" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:publicMethods div $phploc/pdx:methods * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent2">Non-Public Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:nonPublicMethods" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:nonPublicMethods div $phploc/pdx:methods * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:functions" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Named Functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:namedFunctions" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:namedFunctions div $phploc/pdx:functions * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Anonymous Functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:anonymousFunctions" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:anonymousFunctions div $phploc/pdx:functions * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Constants</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:constants" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Global Constants</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:globalConstants" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:globalConstants div $phploc/pdx:constants * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Class Constants</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:classConstants" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:classConstants div $phploc/pdx:constants * 100,'0.##')" />%)</td>
                    </tr>
                </table>
            </div>
            <div class="container">
                <h2>Tests</h2>
                <table class="styled overview">
                    <tr>
                        <td>Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:testClasses" /></td>
                        <td class="percent"/>
                    </tr>
                    <tr>
                        <td>Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:testMethods" /></td>
                        <td class="percent"/>
                    </tr>
                </table>
            </div>
        </div>
        <div class="column">
            <div class="container">
                <h2>Size</h2>
                <table class="styled overview">
                    <tr>
                        <td>Lines of Code (LOC)</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:loc" /></td>
                        <td/>
                    </tr>
                    <tr>
                        <td>Comment Lines of Code (CLOC)</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:cloc" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:cloc div $phploc/pdx:loc * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Non-Comment Lines of Code (NCLOC)</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:ncloc" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:ncloc div $phploc/pdx:loc * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Logical Lines of Code (LLOC)</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:lloc" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:lloc div $phploc/pdx:loc * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:llocClasses" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:llocClasses div $phploc/pdx:lloc * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Average Class Length</td>
                        <td class="nummeric"><xsl:value-of select="round($phploc/pdx:classLlocAvg)" /></td>
                        <td/>
                    </tr>
                    <tr>
                        <td class="indent">Average Method Length</td>
                        <td class="nummeric"><xsl:value-of select="round($phploc/pdx:methodLlocAvg)" /></td>
                        <td/>
                    </tr>
                    <tr>
                        <td>Functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:llocFunctions" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:llocFunctions div $phploc/pdx:lloc * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Average Function Length</td>
                        <td class="nummeric"><xsl:value-of select="round($phploc/pdx:llocByNof)" /></td>
                        <td/>
                    </tr>
                    <tr>
                        <td>Not in classes or functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:llocGlobal" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:llocGlobal div $phploc/pdx:lloc * 100,'0.##')" />%)</td>
                    </tr>
                </table>
            </div>

            <div class="container">
                <h2>Complexity</h2>
                <table class="styled overview">
                    <tr>
                        <td>Cyclomatic Complexity / LLOC</td>
                        <td class="nummeric"><xsl:value-of select="pdxf:format-number($phploc/pdx:ccnByLloc, '0.##')" /></td>
                        <td class="percent"/>
                    </tr>
                    <tr>
                        <td>Cyclomatic Complexity / Number of Methods</td>
                        <td class="nummeric"><xsl:value-of select="pdxf:format-number($phploc/pdx:ccnByNom, '0.##')" /></td>
                        <td class="percent"/>
                    </tr>
                </table>
            </div>

            <div class="container">
                <h2>Dependencies</h2>
                <table class="styled overview">
                    <tr>
                        <td>Global Accesses</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:globalAccesses" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Global Constants</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:globalConstantAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:globalConstantAccesses div $phploc/pdx:globalAccesses * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Global Variables</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:globalVariableAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:globalVariableAccesses div $phploc/pdx:globalAccesses * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Super-Global Variables</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:superGlobalVariableAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:superGlobalVariableAccesses div $phploc/pdx:globalAccesses * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Attribute Accesses</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:attributeAccesses" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Non-Static</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:instanceAttributeAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:instanceAttributeAccesses div $phploc/pdx:attributeAccesses * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Static</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:staticAttributeAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:staticAttributeAccesses div $phploc/pdx:attributeAccesses * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Method Calls</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:methodCalls" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Non-Static</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:instanceMethodCalls" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:instanceMethodCalls div $phploc/pdx:methodCalls * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Static</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/pdx:staticMethodCalls" /></td>
                        <td class="percent">(<xsl:value-of select="pdxf:format-number($phploc/pdx:staticMethodCalls div $phploc/pdx:methodCalls * 100,'0.##')" />%)</td>
                    </tr>
                </table>
            </div>

        </div>
    </xsl:template>

</xsl:stylesheet>
