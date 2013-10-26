<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:phpdox="http://xml.phpdox.de/src#">

    <xsl:import href="components.xsl" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head" />
            <body>
                <xsl:call-template name="nav" />
                <xsl:call-template name="index" />
                <div id="mainstage">
                    <xsl:choose>
                        <xsl:when test="//phpdox:enrichment[@type='phploc']">
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
                <h1>phpDox Project</h1>
                <h2>Software Documentation</h2>
                <p>Welcome to the Software Documentation homepage.</p>
            </div>
            <div id="buildinfo">
                <h3>Build</h3>
                <p><xsl:value-of select="//phpdox:enrichment[@type='build']/phpdox:date/@rfc" /></p>
                <h3>VCS Info</h3>
                <p>
                    <xsl:variable name="current" select="//phpdox:enrichment[@type='git']/phpdox:current" />
                    tag: <xsl:value-of select="$current/@describe" /><br/>
                    branch: <xsl:value-of select="$current/@branch" />
                </p>
                <h3>Used Enrichers</h3>
                <p>
                    <xsl:for-each select="//phpdox:enrichment[@type='build']//phpdox:enricher">
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
        <xsl:call-template name="phploc" />
    </xsl:template>

    <xsl:template name="phploc">
        <xsl:variable name="phploc" select="//phpdox:enrichment[@type='phploc']" />

        <div class="column">
            <div class="container">
                <h2>Structure</h2>
                <table class="styled overview">
                    <tr>
                        <td>Namespaces</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:namespaces" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Interfaces</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:interfaces" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Traits</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:traits" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:classes" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Abstract Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:abstractClasses" /></td>
                        <td class="percent">(<xsl:value-of select="format-number($phploc/phpdox:abstractClasses div $phploc/phpdox:classes * 100, '0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Concrete Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:concreteClasses" /></td>
                        <td class="percent">(<xsl:value-of select="format-number($phploc/phpdox:concreteClasses div $phploc/phpdox:classes * 100,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:methods" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Scope</td>
                        <td />
                        <td />
                    </tr>
                    <tr>
                        <td class="indent2">Non-Static Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:nonStaticMethods" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent2">Static Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:staticMethods" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Visibility</td>
                        <td />
                        <td />
                    </tr>
                    <tr>
                        <td class="indent2">Public Method</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:publicMethods" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent2">Non-Public Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:nonPublicMethods" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:functions" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Named Functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:namedFunctions" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Anonymous Functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:anonymousFunctions" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Constants</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:constants" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td class="indent">Global Constants</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:globalConstants" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Class Constants</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:classConstants" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                </table>
            </div>
            <div class="container">
                <h2>Tests</h2>
                <table class="styled overview">
                    <tr>
                        <td>Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:testClasses" /></td>
                        <td class="percent"/>
                    </tr>
                    <tr>
                        <td>Methods</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:testMethods" /></td>
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
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:loc" /></td>
                        <td/>
                    </tr>
                    <tr>
                        <td>Comment Lines of Code (CLOC)</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:cloc" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Non-Comment Lines of Code (NCLOC)</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:ncloc" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Logical Lines of Code (LLOC)</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:lloc" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Classes</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:llocClasses" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Average Class Length</td>
                        <td class="nummeric"><xsl:value-of select="round($phploc/phpdox:llocByNoc)" /></td>
                        <td/>
                    </tr>
                    <tr>
                        <td class="indent">Average Method Length</td>
                        <td class="nummeric"><xsl:value-of select="round($phploc/phpdox:llocByNom)" /></td>
                        <td/>
                    </tr>
                    <tr>
                        <td>Functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:llocFunctions" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td class="indent">Average Function Length</td>
                        <td class="nummeric"><xsl:value-of select="round($phploc/phpdox:llocByNof)" /></td>
                        <td/>
                    </tr>
                    <tr>
                        <td>Not in classes or functions</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:llocGlobal" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                </table>
            </div>

            <div class="container">
                <h2>Complexity</h2>
                <table class="styled overview">
                    <tr>
                        <td>Cyclomatic Complexity / LLOC</td>
                        <td class="nummeric"><xsl:value-of select="format-number($phploc/phpdox:ccnByLloc, '0.##')" /></td>
                        <td class="percent"/>
                    </tr>
                    <tr>
                        <td>Cyclomatic Complexity / Number of Methods</td>
                        <td class="nummeric"><xsl:value-of select="format-number($phploc/phpdox:ccnByNom, '0.##')" /></td>
                        <td class="percent"/>
                    </tr>
                </table>
            </div>

            <div class="container">
                <h2>Dependencies</h2>
                <table class="styled overview">
                    <tr>
                        <td>Global Accesses</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:globalAccesses" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Global Constants</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:globalConstantAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Global Variables</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:globalVariableAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Super-Global Variables</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:superGlobalVariableAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Attribute Accesses</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:attributeAccesses" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Non-Static</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:instanceAttributeAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Static</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:staticAttributeAccesses" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Method Calls</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:methodCalls" /></td>
                        <td />
                    </tr>
                    <tr>
                        <td>Non-Static</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:instanceMethodCalls" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                    <tr>
                        <td>Static</td>
                        <td class="nummeric"><xsl:value-of select="$phploc/phpdox:staticMethodCalls" /></td>
                        <td class="percent">(<xsl:value-of select="format-number(0,'0.##')" />%)</td>
                    </tr>
                </table>
            </div>

        </div>
    </xsl:template>

</xsl:stylesheet>