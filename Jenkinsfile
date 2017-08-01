#!groovy

node {
    env.WORKSPACE = pwd()
    def buildDir = env.WORKSPACE + '/build'
    def sourceDir = env.WORKSPACE + '/source'
    def jobName = currentBuild.projectName
    def exportDir = buildDir + '/export'
    def packageDir = exportDir + '/' + jobName
    def archiveDir = buildDir + '/archive'
    def docPath = packageDir + '/docs/en'
    def checksumPath = archiveDir + '/' + jobName + '-checksums'
    def artifacts = archiveDir + '/**'

    stage('Prepare') {
        sh 'rm -rf ${buildDir}'
        /*
        sh 'rm -rf ${sourceDir}/src/vendor'
        sh 'rm -f ${sourceDir}/composer.lock'
        sh 'rm -f ${sourceDir}/composer.phar'
        */

        sh 'mkdir ${buildDir}'
        sh 'mkdir ${exportDir}'
        sh 'mkdir ${packageDir}'
        sh 'mkdir ${archiveDir}'
        sh 'mkdir ${buildDir}/api'
        sh 'mkdir ${buildDir}/phpdox'
    }
    stage('Composer Install') {
        sh 'cd ${sourceDir} && wget -nc "http://getcomposer.org/composer.phar" && cd ..'
        sh 'chmod +x ${sourceDir}/composer.phar'
        sh '${sourceDir}/composer.phar install --prefer-dist --no-dev --optimize-autoloader --no-scripts'
        sh '${sourceDir}/composer.phar run-script post-autoload-dump'
        sh '${sourceDir}/composer.phar run-script post-install-cmd'
    }
    stage('Pre-process') {
        echo 'Copying sources to package directory...'
        sh 'cp -a ${sourceDir}/src ${packageDir}'

        echo 'Generating composer_vendors file...'
        sh '${sourceDir}/build.php build:generate_vendor_doc --write-to \'${packageDir}/docs/Composer_Vendors.md\''

        echo 'Generating LESS file...'
        sh '${sourceDir}/build.php build:generate_less --write-to \'${packageDir}/web/bootstrap-font-awesome.css\''

        echo 'Moving docs and composer files to /docs/en ...'
        sh 'mv -f ${sourceDir}/README.md ${docPath}/README.md'
        sh 'cp -f ${sourceDir}/INSTALL-2.0.md ${packageDir}/INSTALL-2.0.md'
        sh 'mv -f ${sourceDir}/INSTALL-2.0.md ${docPath}/INSTALL-2.0.md'
        sh 'cp -f ${sourceDir}/UPGRADE-2.0.md ${packageDir}/UPGRADE-2.0.md'
        sh 'mv -f ${sourceDir}/UPGRADE-2.0.md ${docPath}/UPGRADE-2.0.md'
        sh 'mv -f ${sourceDir}/CHANGELOG-2.0.md ${docPath}/CHANGELOG-2.0.md'
        sh 'mv -f ${sourceDir}/composer.json ${docPath}/dev/composer.json'
        sh 'mv -f ${sourceDir}/composer.lock ${docPath}/dev/composer.lock'

        echo 'Purging tests from vendors...'
        sh '${sourceDir}/build.php build:purge_vendors ${packageDir}/vendor'
        echo 'Fixing autoloader paths...'
        sh '${sourceDir}/build.php build:fix_autoloader ${packageDir}/vendor'
    }
    stage('Create translations') {
        echo 'Creating translation files...'
        sh 'php -dmemory_limit=2G ${packageDir}/bin/console translation:extract template --output-format=po --output-dir=${packageDir}/app/Resources/translations --enable-extractor=jms_i18n_routing --dir=${packageDir}/system --dir=${packageDir}/lib/Zikula/Bundle'
    }
    stage('Post-processing') {
        echo 'Clearing cache directory...'
        sh 'find ${packageDir}/var/cache -type \'f\' | grep -v ".htaccess" | xargs rm '
        sh 'find ${packageDir}/var/cache -type \'d\' | xargs rmdir'
        echo 'Clearing log directory...'
        sh 'find ${packageDir}/var/logs -type \'f\' | grep -v ".htaccess" | xargs rm '
        sh 'find ${packageDir}/var/logs -type \'d\' | xargs rmdir'

        echo 'Setting directory permissions...'
        sh 'chmod -R 0777 ${packageDir}/app/config'
        sh 'chmod -R 0777 ${packageDir}/app/config/dynamic'
        sh 'chmod -R 0777 ${packageDir}/var/cache'
        sh 'chmod -R 0777 ${packageDir}/var/logs'
    }
    stage('Create archives') {
        echo 'Creating archives...'
        sh 'cd ${exportDir}; zip -D -r ${archiveDir}/${jobName}.zip .'
        sh 'cd ${exportDir}; tar cp ${jobName} > ${archiveDir}/${jobName}.tar; gzip ${archiveDir}/${jobName}.tar'
    }
    stage('Build checksums') {
        echo 'Creating MD5 and SHA1 checksums...'
        sh 'echo -----------------md5sums----------------- > ${checksumPath}.tmp'
        sh 'md5sum ${archiveDir}/*.tar.gz ${archiveDir}/*.zip >> ${checksumPath}.tmp'
        sh 'echo -----------------sha1sums----------------- >> ${checksumPath}.tmp'
        sh 'sha1sum ${archiveDir}/*.tar.gz ${archiveDir}/*.zip >> ${checksumPath}.tmp'

        sh 'cat ${checksumPath}.tmp | sed 's!${archiveDir}/!!g' > ${checksumPath}.txt'
        sh 'rm -f ${checksumPath}.tmp'
    }
    stage('Generate documentation') {
        sh 'vendor/bin/phpdox -f build/phpdox.xml'
    }
    stage('Archive artifacts') {
        echo 'Archiving the artifacts...'
        archiveArtifacts([
            artifacts: artifacts,
            fingerprint: true,
            onlyIfSuccessful: true
        ])
    }
}
