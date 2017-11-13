#!groovy

properties([
    buildDiscarder(logRotator(numToKeepStr: '3'))
])

node {
    env.WORKSPACE = pwd()
    def buildDir = env.WORKSPACE + '/build_work'
    def sourceDir = env.WORKSPACE
    def jobName = currentBuild.projectName
    def exportDir = buildDir + '/export'
    def packageDir = exportDir + '/' + jobName
    def archiveDir = buildDir + '/archive'
    def composerFile = sourceDir + '/composer.phar'
    def phpBuild = sourceDir + '/build.php'

    def docPath = packageDir + '/docs/en'
    def copyFiles = [
        'README.md': [docPath],
        'INSTALL-Core1.x.md': [packageDir, docPath],
        'UPGRADE-Core1.x.md': [packageDir, docPath],
        'CHANGELOG-1.4.md': [docPath],
        'CHANGELOG-1.5.md': [docPath],
        'ROADMAP.md': [docPath],
        'composer.json': [docPath + '/dev'],
        'composer.lock': [docPath + '/dev']
    ]

    def checksumPath = archiveDir + '/' + jobName + '-checksums'
    def artifacts = 'build_work/archive/**'

    stage('Prepare') {
        echo 'Generating legacy translations...'
        build 'Zikula_Core-1.5-POT'

        echo 'Checkout from Git repository...'
        checkout scm

        sh 'rm -rf ' + buildDir
        /*
        sh 'rm -rf ' + sourceDir + '/src/vendor'
        */

        sh 'mkdir ' + buildDir
        sh 'mkdir ' + exportDir
        sh 'mkdir ' + packageDir
        sh 'mkdir ' + archiveDir
    }
    stage('Composer Install') {
        sh 'cd ' + sourceDir + ' && wget -nc "http://getcomposer.org/composer.phar"'
        sh 'chmod +x ' + composerFile
        sh composerFile + ' self-update'
        sh composerFile + ' install --prefer-dist --no-dev --optimize-autoloader --no-scripts'
        sh composerFile + ' run-script post-autoload-dump'
        sh composerFile + ' run-script post-install-cmd'
    }
    stage('Pre-process') {
        echo 'Copying sources to package directory...'
        sh 'cp -a ' + sourceDir + '/src/. ' + packageDir

        echo 'Generating composer_vendors file...'
        sh phpBuild + ' build:generate_vendor_doc --write-to \'' + packageDir + '/docs/Composer_Vendors.md\''

        echo 'Generating LESS file...'
        sh phpBuild + ' build:generate_less --write-to \'' + packageDir + '/web/bootstrap-font-awesome.css\''

        echo 'Moving docs and composer files to /docs/en ...'
        sh 'mkdir -p ' + docPath + '/dev'
        copyFiles.each { fileName, folders ->
            folders.each { folder ->
                sh 'cp -f ' + sourceDir + '/' + fileName + ' ' + folder + '/'
            }
        }

        echo 'Purging tests from vendors...'
        sh phpBuild + ' build:purge_vendors --vendor-dir ' + packageDir + '/vendor'
        echo 'Fixing autoloader paths...'
        sh phpBuild + ' build:fix_autoloader --vendor-dir ' + packageDir + '/vendor'
    }
    stage('Create translations') {
        echo 'Copying legacy translations...'
        sh 'cp /var/lib/jenkins/jobs/Zikula_Core-1.5-POT/lastSuccessful/archive/source/src/app/Resources/locale/zikula.pot ' + packageDir + '/app/Resources/locale/zikula.pot'
        sh 'cp /var/lib/jenkins/jobs/Zikula_Core-1.5-POT/lastSuccessful/archive/source/src/app/Resources/locale/zikula_js.pot ' + packageDir + '/app/Resources/locale/zikula_js.pot'
        echo 'Creating translation files...'
        sh 'php -dmemory_limit=2G ' + packageDir + '/app/console translation:extract template --output-format=po --output-dir=' + packageDir + '/app/Resources/translations --enable-extractor=jms_i18n_routing --dir=' + packageDir + '/system --dir=' + packageDir + '/lib/Zikula/Bundle'
    }
    stage('Post-processing') {
        echo 'Clearing cache directory...'
        sh 'mv ' + packageDir + '/app/cache/.htaccess ' + packageDir + '/app/'
        sh 'rm -rf ' + packageDir + '/app/cache/*'
        sh 'mv ' + packageDir + '/app/.htaccess ' + packageDir + '/app/cache/'

        echo 'Clearing log directory...'
        sh 'mv ' + packageDir + '/app/logs/.htaccess ' + packageDir + '/app/'
        sh 'rm -rf ' + packageDir + '/app/logs/*'
        sh 'mv ' + packageDir + '/app/.htaccess ' + packageDir + '/app/logs/'

        echo 'Setting directory permissions...'
        sh 'chmod -R 0777 ' + packageDir + '/app/config'
        sh 'chmod -R 0777 ' + packageDir + '/app/config/dynamic'
        sh 'chmod -R 0777 ' + packageDir + '/app/cache'
        sh 'chmod -R 0777 ' + packageDir + '/app/logs'
        sh 'chmod -R 0777 ' + packageDir + '/config'
        sh 'chmod -R 0777 ' + packageDir + '/userdata'
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

        sh 'cat ' + tmpFile + ' | sed \'s!' + archiveDir + '/!!g\' > ' + checksumPath + '.txt'
        sh 'rm -f ' + tmpFile
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
