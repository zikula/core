#!groovy

node {
    env.WORKSPACE = pwd()
    def buildDir = env.WORKSPACE + '/build'
    def sourceDir = env.WORKSPACE + '/source'
    def jobName = currentBuild.projectName
    def exportDir = buildDir + '/export'
    def packageDir = exportDir + '/' + jobName
    def archiveDir = buildDir + '/archive'
    def composerFile = sourceDir + '/composer.phar'
    def phpBuild = sourceDir + '/build.php'
    def docPath = packageDir + '/docs/en'
    def checksumPath = archiveDir + '/' + jobName + '-checksums'
    def artifacts = archiveDir + '/**'

    stage('Prepare') {
        sh 'rm -rf ' + buildDir
        /*
        sh 'rm -rf ' + sourceDir + '/src/vendor'
        sh 'rm -f ' + sourceDir + '/composer.lock'
        sh 'rm -f ' + composerFile
        */

        sh 'mkdir ' + sourceDir
        sh 'mv ' + env.WORKSPACE + '/* ' + sourceDir + '/'
        sh 'mkdir ' + buildDir
        sh 'mkdir ' + exportDir
        sh 'mkdir ' + packageDir
        sh 'mkdir ' + archiveDir
        sh 'mkdir ' + buildDir + '/api'
        sh 'mkdir ' + buildDir + '/phpdox'
    }
    stage('Composer Install') {
        sh 'cd ' + sourceDir + ' && wget -nc "http://getcomposer.org/composer.phar" && cd ..'
        sh 'chmod +x ' + composerFile
        sh composerFile + ' install --prefer-dist --no-dev --optimize-autoloader --no-scripts'
        sh composerFile + ' run-script post-autoload-dump'
        sh composerFile + ' run-script post-install-cmd'
    }
    stage('Pre-process') {
        echo 'Copying sources to package directory...'
        sh 'cp -a ' + sourceDir + '/src ' + packageDir

        echo 'Generating composer_vendors file...'
        sh phpBuild + ' build:generate_vendor_doc --write-to \'' + packageDir + '/docs/Composer_Vendors.md\''

        echo 'Generating LESS file...'
        sh phpBuild + ' build:generate_less --write-to \'' + packageDir + '/web/bootstrap-font-awesome.css\''

        echo 'Moving docs and composer files to /docs/en ...'
        sh 'mv -f ' + sourceDir + '/README.md ' + docPath + '/README.md'
        sh 'cp -f ' + sourceDir + '/INSTALL-2.0.md ' + packageDir + '/INSTALL-2.0.md'
        sh 'mv -f ' + sourceDir + '/INSTALL-2.0.md ' + docPath + '/INSTALL-2.0.md'
        sh 'cp -f ' + sourceDir + '/UPGRADE-2.0.md ' + packageDir + '/UPGRADE-2.0.md'
        sh 'mv -f ' + sourceDir + '/UPGRADE-2.0.md ' + docPath + '/UPGRADE-2.0.md'
        sh 'mv -f ' + sourceDir + '/CHANGELOG-2.0.md ' + docPath + '/CHANGELOG-2.0.md'
        sh 'mv -f ' + sourceDir + '/composer.json ' + docPath + '/dev/composer.json'
        sh 'mv -f ' + sourceDir + '/composer.lock ' + docPath + '/dev/composer.lock'

        echo 'Purging tests from vendors...'
        sh phpBuild + ' build:purge_vendors ' + packageDir + '/vendor'
        echo 'Fixing autoloader paths...'
        sh phpBuild + ' build:fix_autoloader ' + packageDir + '/vendor'
    }
    stage('Create translations') {
        echo 'Creating translation files...'
        sh 'php -dmemory_limit=2G ' + packageDir + '/bin/console translation:extract template --output-format=po --output-dir=' + packageDir + '/app/Resources/translations --enable-extractor=jms_i18n_routing --dir=' + packageDir + '/system --dir=' + packageDir + '/lib/Zikula/Bundle'
    }
    stage('Post-processing') {
        echo 'Clearing cache directory...'
        sh 'find ' + packageDir + '/var/cache -type \'f\' | grep -v ".htaccess" | xargs rm '
        sh 'find ' + packageDir + '/var/cache -type \'d\' | xargs rmdir'
        echo 'Clearing log directory...'
        sh 'find ' + packageDir + '/var/logs -type \'f\' | grep -v ".htaccess" | xargs rm '
        sh 'find ' + packageDir + '/var/logs -type \'d\' | xargs rmdir'

        echo 'Setting directory permissions...'
        sh 'chmod -R 0777 ' + packageDir + '/app/config'
        sh 'chmod -R 0777 ' + packageDir + '/app/config/dynamic'
        sh 'chmod -R 0777 ' + packageDir + '/var/cache'
        sh 'chmod -R 0777 ' + packageDir + '/var/logs'
    }
    stage('Create archives') {
        echo 'Creating archives...'
        def archiveBasePath = archiveDir + '/' + jobName
        sh 'cd ' + exportDir + '; zip -D -r ' + archiveBasePath + '.zip .'
        sh 'cd ' + exportDir + '; tar cp ' + jobName + ' > ' + archiveBasePath + '.tar; gzip ' + archiveBasePath + '.tar'
    }
    stage('Build checksums') {
        echo 'Creating MD5 and SHA1 checksums...'
        def tmpFile = checksumPath + '.tmp'
        sh 'echo -----------------md5sums----------------- > ' + tmpFile
        sh 'md5sum ' + archiveDir + '/*.tar.gz ' + archiveDir + '/*.zip >> ' + tmpFile
        sh 'echo -----------------sha1sums----------------- >> ' + tmpFile
        sh 'sha1sum ' + archiveDir + '/*.tar.gz ' + archiveDir + '/*.zip >> ' + tmpFile

        sh 'cat ' + tmpFile + ' | sed 's!' + archiveDir + '/!!g' > ' + checksumPath + '.txt'
        sh 'rm -f ' + tmpFile
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
