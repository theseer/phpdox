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

    <xsl:param name="base" select="''" />
    <xsl:param name="xml" select="''" />
    <xsl:param name="extension" select="'xhtml'" />
    <xsl:param name="project" select="'phpDox generated Project'" />

    <xsl:param name="hasNamespaces" select="'N'" />
    <xsl:param name="hasInterfaces" select="'N'" />
    <xsl:param name="hasTraits" select="'N'" />
    <xsl:param name="hasClasses" select="'N'" />
    <xsl:param name="hasReports" select="'N'" />

    <!-- ######################################################################################################### -->

    <xsl:template name="head">
        <xsl:param name="title" select="'Overview'" />

        <head>
            <title>phpDox - <xsl:value-of select="$title" /></title>
            <link rel="stylesheet" type="text/css" href="{$base}css/style.css" media="screen" />
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        </head>

    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="nav">
        <nav class="topnav">
            <ul>
                <li>
                    <div class="logo"><span>/**</span>phpDox</div>
                </li>
                <li class="separator"><a href="{$base}index.{$extension}">Overview</a></li>
                <xsl:if test="$hasNamespaces = 'Y'">
                    <li class="separator"><a href="{$base}namespaces.{$extension}">Namespaces</a></li>
                </xsl:if>
                <xsl:if test="$hasInterfaces = 'Y'">
                    <li><a href="{$base}interfaces.{$extension}">Interfaces</a></li>
                </xsl:if>
                <xsl:if test="$hasClasses = 'Y'">
                    <li><a href="{$base}classes.{$extension}">Classes</a></li>
                </xsl:if>
                <xsl:if test="$hasTraits = 'Y'">
                    <li><a href="{$base}traits.{$extension}">Traits</a></li>
                </xsl:if>
                <li class="separator"><a href="{$base}source/index.{$extension}">Source</a></li>
                <!--<li class="separator"><a href="{$base}reports/index.{$extension}">Reports</a></li>-->
            </ul>
        </nav>

    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="footer">
        <footer>
            <span><xsl:value-of select="//pdx:enrichment[@type = 'build']/pdx:phpdox/@generated" /></span>
        </footer>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="docblock">
        <xsl:param name="ctx" select="$unit" />

        <xsl:for-each select="$ctx/pdx:docblock">
            <ul>
                <xsl:if test="pdx:author">
                    <li>Author: <xsl:value-of select="pdx:author/@value" /></li>
                </xsl:if>
                <xsl:if test="pdx:copyright">
                    <li>Copyright: <xsl:value-of select="pdx:copyright/@value" /></li>
                </xsl:if>
                <xsl:if test="pdx:license">
                    <li>License: <xsl:value-of select="pdx:license/@name" /></li>
                </xsl:if>
            </ul>
        </xsl:for-each>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="hierarchy">
        <xsl:param name="dir" select="'classes'" />

        <div class="styled">
            <xsl:if test="$unit/pdx:extends">
                <h4>Extends</h4>
                <ul>
                    <xsl:for-each select="$unit/pdx:extends">
                        <li><xsl:copy-of select="pdxf:link(., '', @full)" /></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="$unit/pdx:extender">
                <h4>Extended by</h4>
                <ul>
                    <xsl:for-each select="$unit/pdx:extender/*">
                        <li><xsl:copy-of select="pdxf:link(., '', @full)" /></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="$unit/pdx:uses">
                <h4>Uses</h4>
                <ul>
                    <xsl:for-each select="$unit/pdx:uses">
                        <li><xsl:copy-of select="pdxf:link(., '', @full)" /></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="$unit/pdx:implements">
                <h4>Implements</h4>
                <ul>
                    <xsl:for-each select="$unit/pdx:implements">
                        <li><xsl:copy-of select="pdxf:link(., '', @full)" /></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="$unit/pdx:implementor">
                <h4>Implemented by</h4>
                <ul>
                    <xsl:for-each select="$unit/pdx:implementor">
                        <li><xsl:copy-of select="pdxf:link(., '', @full)" /></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="$unit/pdx:users">
                <h4>Used by</h4>
                <ul>
                    <xsl:for-each select="$unit/pdx:users/*">
                        <li><xsl:copy-of select="pdxf:link(., '', @full)" /></li>
                    </xsl:for-each>
                </ul>
            </xsl:if>
        </div>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="violations">
        <xsl:param name="ctx" />

        <xsl:if test="$ctx/pdx:enrichment[@type='pmd' or @type='checkstyle']">
        <h2 id="violations">Violations</h2>
        <div class="styled">
            <xsl:if test="$ctx/pdx:enrichment[@type='pmd']">
                <h3>PHPMessDetector</h3>
                <table class="styled">
                    <thead>
                        <tr>
                            <th>Line</th>
                            <th>Rule</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <xsl:for-each select="$ctx/pdx:enrichment[@type='pmd']/pdx:violation">
                        <xsl:sort data-type="number" select="@beginline" order="ascending" />
                        <tr>
                            <td class="line">
                                <xsl:choose>
                                    <xsl:when test="@beginline = @endline"><xsl:value-of select="@beginline" /></xsl:when>
                                    <xsl:otherwise><xsl:value-of select="@beginline" /> - <xsl:value-of select="@endline" /></xsl:otherwise>
                                </xsl:choose>
                            </td>
                            <td><a href="{@externalInfoUrl}" target="_blank" title="{@ruleset}"><xsl:value-of select="@rule" /></a></td>
                            <td><xsl:value-of select="@message" /></td>
                        </tr>
                    </xsl:for-each>
                </table>
            </xsl:if>
            <xsl:if test="$ctx/pdx:enrichment[@type='checkstyle']">
                <h3>Checkstyle</h3>
                <table class="styled">
                    <thead>
                        <tr>
                            <th>Line</th>
                            <th>Column</th>
                            <th>Severity</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <xsl:for-each select="$ctx/pdx:enrichment[@type='checkstyle']/pdx:*">
                        <xsl:sort data-type="number" select="@line" order="ascending" />
                        <tr>
                            <td class="line"><xsl:value-of select="@line" /></td>
                            <td><xsl:value-of select="@column" /></td>
                            <td><span title="{@source}"><xsl:value-of select="local-name(.)" /></span></td>
                            <td><xsl:value-of select="@message" /></td>
                        </tr>
                    </xsl:for-each>
                </table>
            </xsl:if>
        </div>
        </xsl:if>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="tasks">
        <xsl:param name="ctx" select="$unit" />
        <table class="styled">
            <thead>
                <tr>
                    <th style="width:3em;">Line</th>
                    <th>Task</th>
                </tr>
            </thead>
            <xsl:for-each select="$ctx//pdx:todo">
                <xsl:variable name="line">
                    <xsl:choose>
                        <xsl:when test="@line"><xsl:value-of select="@line" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="../../@start" />+</xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                <tr>
                    <td class="nummeric"><xsl:value-of select="$line" /></td>
                    <td><xsl:value-of select="@value" /></td>
                </tr>
            </xsl:for-each>
        </table>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="constants">
        <table class="styled">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="//pdx:constant">
                    <tr>
                        <td id="{@name}"><xsl:value-of select="@name" /></td>
                        <td><xsl:choose>
                            <xsl:when test="@value = ''"><xsl:value-of select="@constant" /></xsl:when>
                            <xsl:otherwise><xsl:value-of select="@value" /></xsl:otherwise>
                        </xsl:choose></td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="members">
        <div class="styled members">
            <xsl:if test="//pdx:member[@visibility='private']">
                <h4>private</h4>
                <ul class="members">
                    <xsl:for-each select="//pdx:member[@visibility='private']">
                        <xsl:sort select="@name" />
                        <xsl:call-template name="memberli" />
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="//pdx:member[@visibility='protected']">
                <h4>protected</h4>
                <ul class="members">
                    <xsl:for-each select="//pdx:member[@visibility='protected']">
                        <xsl:sort select="@name" />
                        <xsl:call-template name="memberli" />
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="//pdx:member[@visibility='public']">
                <h4>public</h4>
                <ul class="members">
                    <xsl:for-each select="//pdx:member[@visibility='public']">
                        <xsl:sort select="@name" />
                        <xsl:call-template name="memberli" />
                    </xsl:for-each>
                </ul>
            </xsl:if>
        </div>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="memberli">
        <li id="{@name}">
            <strong>$<xsl:value-of select="@name" /></strong>
            <xsl:if test="pdx:docblock/pdx:var">
                —
                <xsl:choose>
                    <xsl:when test="pdx:docblock/pdx:var/@type = 'object'">
                        <xsl:variable name="ctx" select="pdx:docblock/pdx:var/pdx:type" />
                        <xsl:copy-of select="pdxf:link($ctx, '', $ctx/@full)" />
                    </xsl:when>
                    <xsl:otherwise><xsl:value-of select="pdx:docblock/pdx:var/@type" /></xsl:otherwise>
                </xsl:choose>
            </xsl:if>
            <xsl:if test="pdx:docblock/pdx:description/@compact != ''">
                <br/>
                <span class="indent">
                    <xsl:value-of select="pdx:docblock/pdx:description/@compact" />
                </span>
            </xsl:if>
        </li>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="methods">
        <div class="styled">
            <xsl:if test="$unit/pdx:method[@visibility='private']">
                <h4>private</h4>
                <xsl:call-template name="method-ul">
                    <xsl:with-param name="isParent" select="'false'" />
                    <xsl:with-param name="visibility" select="'private'" />
                    <xsl:with-param name="ctx" select="$unit" />
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="$unit/pdx:method[@visibility='protected']">
                <h4>protected</h4>
                <xsl:call-template name="method-ul">
                    <xsl:with-param name="isParent" select="'false'" />
                    <xsl:with-param name="visibility" select="'protected'" />
                    <xsl:with-param name="ctx" select="$unit" />
                </xsl:call-template>
            </xsl:if>
            <xsl:if test="$unit/pdx:method[@visibility='public']">
                <h4>public</h4>
                <xsl:call-template name="method-ul">
                    <xsl:with-param name="isParent" select="'false'" />
                    <xsl:with-param name="visibility" select="'public'" />
                    <xsl:with-param name="ctx" select="$unit" />
                </xsl:call-template>
            </xsl:if>
            <xsl:call-template name="inheritedMethods">
                <xsl:with-param name="ctx" select="$unit" />
            </xsl:call-template>

        </div>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="method-ul">
        <xsl:param name="visibility" />
        <xsl:param name="ctx" />
        <xsl:param name="isParent" select="'true'" />
        <ul>
            <xsl:if test="$isParent != 'true'">
                <xsl:for-each select="$unit/pdx:constructor[@visibility = $visibility]|$unit/pdx:destructor[@visibility = $visibility]">
                    <xsl:call-template name="method-li" />
                </xsl:for-each>
            </xsl:if>
            <xsl:for-each select="$ctx/pdx:method[@visibility=$visibility]">
                <xsl:sort select="@name" />
                <xsl:call-template name="method-li" />
            </xsl:for-each>
        </ul>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="method-li">
        <li id="{@name}">
            <xsl:variable name="title">
            <xsl:choose>
                <xsl:when test="@original"><xsl:value-of select="@original" /></xsl:when>
                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
            </xsl:choose>
            </xsl:variable>
            <xsl:copy-of select="pdxf:link(parent::*[1], $title, concat(@name, '()'))" />
            <xsl:if test="pdx:docblock/pdx:description/@compact != ''">
                — <xsl:value-of select="pdx:docblock/pdx:description/@compact" />
            </xsl:if>
        </li>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="inheritedMethods">
        <xsl:param name="ctx" />

        <xsl:for-each select="//pdx:parent|$unit//pdx:trait">
            <xsl:variable name="parent" select="." />

            <xsl:if test="count($parent/pdx:method) > 0">
                <h3>Inherited from <xsl:copy-of select="pdxf:link($parent, '', $parent/@full)" /></h3>
            </xsl:if>
            <xsl:if test="$parent/pdx:method[@visibility='protected']">
                <h4>protected</h4>
                <ul>
                    <xsl:for-each select="$parent/pdx:method[@visibility='protected']">
                        <xsl:sort select="@name" />
                        <xsl:variable name="name" select="@name" />
                        <xsl:if test="not($unit/pdx:mehthod[@name = $name])">
                            <xsl:call-template name="method-li" />
                        </xsl:if>
                    </xsl:for-each>
                </ul>
            </xsl:if>
            <xsl:if test="$parent/pdx:method[@visibility='public']">
                <h4>public</h4>
                <ul>
                    <xsl:for-each select="$parent/pdx:method[@visibility='public']">
                        <xsl:sort select="@name" />
                        <xsl:variable name="name" select="@name" />
                        <xsl:if test="not($unit/pdx:mehthod[@name = $name])">
                            <xsl:call-template name="method-li" />
                        </xsl:if>
                    </xsl:for-each>
                </ul>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="type">
        <xsl:param name="ctx" />
        <xsl:choose>
            <xsl:when test="$ctx/pdx:docblock/pdx:return/@type = 'object'"><xsl:value-of select="$ctx/pdx:docblock/pdx:return/pdx:type/@name" /></xsl:when>
            <xsl:when test="not($ctx/pdx:docblock/pdx:return)">void</xsl:when>
            <xsl:otherwise><xsl:value-of select="$ctx/pdx:docblock/pdx:return/@type" /></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="git-history">
        <ul class="styled history">
        <xsl:for-each select="//pdx:enrichment[@type = 'git']/git:commit">
            <xsl:sort data-type="number" select="git:commiter/@unixtime" order="descending" />
            <li>
                <h3><xsl:value-of select="git:commiter/@time" /> (commit #<span title="{@sha1}"><xsl:value-of select="substring(@sha1,0,8)" /></span>)</h3>
                <div>
                    <p>
                        Author: <xsl:value-of select="git:author/@name" /> (<xsl:value-of select="git:author/@email" />) /
                        Commiter: <xsl:value-of select="git:commiter/@name" /> (<xsl:value-of select="git:author/@email" />)
                    </p>
                    <pre class="wrapped"><xsl:value-of select="git:message" /></pre>
                </div>
            </li>
        </xsl:for-each>
        </ul>
    </xsl:template>

</xsl:stylesheet>
