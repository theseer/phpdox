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

    <xsl:variable name="type">
        <xsl:choose>
            <xsl:when test="local-name($unit) = 'class'">classes</xsl:when>
            <xsl:when test="local-name($unit) = 'trait'">traits</xsl:when>
            <xsl:when test="local-name($unit) = 'interface'">interfaces</xsl:when>
        </xsl:choose>
    </xsl:variable>

    <xsl:template name="synopsis">
        <xsl:param name="unit" />

        <div class="synopsis">
            <xsl:value-of select="local-name($unit)" />&#160;<xsl:value-of select="$unit/@name" />
            <xsl:if test="$unit/pdx:extends">
                extends <xsl:for-each select="$unit/pdx:extends">
                    <xsl:copy-of select="pdxf:link(., '', @name)" />
                    <xsl:if test="position() != last()">, </xsl:if>
                </xsl:for-each>

            </xsl:if>
            <xsl:if test="$unit/pdx:implements">
                implements
                <xsl:for-each select="$unit/pdx:implements">
                    <xsl:copy-of select="pdxf:link(., '', @name)" />
                    <xsl:if test="position() != last()">,</xsl:if>
                </xsl:for-each>
            </xsl:if>
            {<br/>
            <xsl:if test="$unit/pdx:constant">
                <ul class="none">
                    <li>// constants</li>
                    <xsl:for-each select="$unit/pdx:constant">
                        <li>const <xsl:value-of select="@name" /> = <xsl:choose>
                            <xsl:when test="@value = ''"><xsl:value-of select="@constant" /></xsl:when>
                            <xsl:otherwise><xsl:value-of select="@value" /></xsl:otherwise>
                        </xsl:choose>;</li>
                    </xsl:for-each>
                </ul>
            </xsl:if>

            <xsl:for-each select="$unit/pdx:parent[pdx:constant]">
            <ul class="none">
                <li>// Inherited constants from <xsl:copy-of select="pdxf:link(., '', @name)" /></li>
                <xsl:for-each select="pdx:constant">
                    <li>const <a href="#{@name}"><xsl:value-of select="@name" /></a> = <xsl:choose>
                        <xsl:when test="@value = ''"><xsl:value-of select="@constant" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="@value" /></xsl:otherwise>
                    </xsl:choose>;</li>
                </xsl:for-each>
            </ul>
            </xsl:for-each>

            <xsl:if test="$unit/pdx:member">
            <ul class="none">
                <li>// members</li>
                <xsl:for-each select="$unit/pdx:member">
                    <li>
                        <xsl:value-of select="@visibility" /><xsl:if test="@static = 'true'">&#160;static</xsl:if><xsl:call-template name="vartype" />&#160;<a href="#members">$<xsl:value-of select="@name" /></a><xsl:if test="@default or @constant"> =
                        <xsl:choose>
                            <xsl:when test="@default = ''"> <xsl:value-of select="@constant" /></xsl:when>
                            <xsl:otherwise><xsl:value-of select="@default" /></xsl:otherwise>
                    </xsl:choose></xsl:if>;
                    </li>
                </xsl:for-each>
            </ul>
            </xsl:if>

            <xsl:for-each select="$unit/pdx:parent[pdx:member]">
                <ul class="none">
                    <li>// Inherited members from <span title="{@full}"><xsl:value-of select="@name" /></span></li>
                    <xsl:for-each select="pdx:member">
                        <li>
                            <xsl:value-of select="@visibility" /><xsl:if test="@static = 'true'">&#160;static</xsl:if><xsl:call-template name="vartype" />&#160;<a href="#members">$<xsl:value-of select="@name" /></a>;
                        </li>
                    </xsl:for-each>
                </ul>
            </xsl:for-each>

            <xsl:if test="$unit/pdx:constructor|$unit/pdx:destructor|$unit/pdx:method">
            <ul class="none">
                <li>// methods</li>
                <xsl:for-each select="$unit/pdx:constructor|$unit/pdx:destructor|$unit/pdx:method">
                    <li>
                    <xsl:value-of select="@visibility" /><xsl:if test="@final = 'true'">&#160;final</xsl:if><xsl:if test="@abstract = 'true'">&#160;abstract</xsl:if><xsl:if test="@static = 'true'">&#160;static</xsl:if>&#160;<xsl:call-template
                                name="returntype">
                            <xsl:with-param name="ctx" select="." />
                        </xsl:call-template>&#160;<xsl:copy-of select="pdxf:link($unit, @name, @name)" />()
                    </li>
                </xsl:for-each>
            </ul>
            </xsl:if>

            <xsl:for-each select="$unit/pdx:parent[pdx:method[@visibility != 'private']|pdx:constructor[@visibility != 'private']|pdx:destructor[@visibility != 'private']]|$unit/pdx:trait[pdx:method]">
                <xsl:variable name="parent" select="." />
                <ul class="none">
                    <li>// Inherited methods from <span title="{@full}"><xsl:value-of select="@name" /></span></li>
                    <xsl:for-each select="pdx:constructor[@visibility != 'private']|pdx:destructor[@visibility != 'private']|pdx:method[@visibility != 'private']">
                        <xsl:variable name="title">
                            <xsl:choose>
                                <xsl:when test="@original"><xsl:value-of select="@original" /></xsl:when>
                                <xsl:otherwise><xsl:value-of select="@name" /></xsl:otherwise>
                            </xsl:choose>
                        </xsl:variable>
                        <li>
                            <xsl:value-of select="@visibility" /><xsl:if test="@final = 'true'">&#160;final</xsl:if><xsl:if test="@abstract = 'true'">&#160;abstract</xsl:if><xsl:if test="@static = 'true'">&#160;static</xsl:if>&#160;<xsl:call-template
                                name="type"><xsl:with-param name="ctx" select="." /></xsl:call-template>&#160;<xsl:copy-of select="pdxf:link($parent, $title, @name)" />()
                        </li>
                    </xsl:for-each>
                </ul>
            </xsl:for-each>

            }<br/>
        </div>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="vartype">
        <xsl:choose>
            <xsl:when test="pdx:docblock/pdx:var/@type = 'object'">&#160;<span title="{pdx:docblock/pdx:var/pdx:type/@full}"><xsl:value-of select="pdx:docblock/pdx:var/pdx:type/@name" /></span></xsl:when>
            <xsl:when test="@type = '{unknown}'">
                <xsl:if test="pdx:docblock/pdx:var/@type">&#160;<xsl:value-of select="pdx:docblock/pdx:var/@type" /></xsl:if>
            </xsl:when>
            <xsl:otherwise>&#160;<xsl:value-of select="@type" /></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- ######################################################################################################### -->

    <xsl:template name="returntype">
        <xsl:param name="ctx" />
        <xsl:choose>
            <xsl:when test="pdx:return"><xsl:if test="pdx:return/@nullable = 'true'">?</xsl:if>
                <xsl:choose>
                    <xsl:when test="pdx:return/@type = 'object'"><span title="{pdx:return/pdx:type/@name}"><xsl:value-of select="pdx:return/pdx:type/@name" /></span></xsl:when>
                    <xsl:otherwise><xsl:value-of select="pdx:return/@type" /></xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="pdx:docblock/pdx:return/@type = 'object'"><xsl:value-of select="pdx:docblock/pdx:return/pdx:type/@name" /></xsl:when>
            <xsl:when test="not(pdx:docblock/pdx:return)">void</xsl:when>
            <xsl:otherwise><xsl:value-of select="pdx:docblock/pdx:return/@type" /></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
