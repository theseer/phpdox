<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:idx="http://xml.phpdox.de/src#">

    <xsl:import href="components.xsl" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head" />
            <body>
                <div id="mainstage">
                    <h1>Classes</h1>
                    <xsl:choose>
                        <xsl:when test="//idx:class">
                            <xsl:apply-templates select="//idx:namespace[idx:class]">
                                <xsl:sort select="@name" order="ascending" />
                            </xsl:apply-templates>
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="box">
                                <p>Sorry, no classes found.</p>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>
                </div>
                <xsl:call-template name="footer" />
            </body>
        </html>
    </xsl:template>

    <xsl:template match="idx:namespace">
        <h2><xsl:value-of select="@name" /></h2>
        <table class="styled">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th />
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="idx:class">
                    <tr>
                        <td><a href="#"><xsl:value-of select="@name" /></a></td>
                        <td>The description</td>
                        <td>[Build-State]</td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>


</xsl:stylesheet>