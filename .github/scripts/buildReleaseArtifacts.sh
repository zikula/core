#!/bin/bash

WORKSPACE_ROOT="${GITHUB_WORKSPACE}/"
BUILD_PATH="${WORKSPACE_ROOT}build_work"
SOURCE_PATH="${GITHUB_WORKSPACE}"
BRANCH_PREFIX="refs/heads/"
BRANCH_NAME=${GITHUB_REF#"$BRANCH_PREFIX"}
EXPORT_PATH="${BUILD_PATH}/export"
PACKAGE_PATH="${EXPORT_PATH}/${BRANCH_NAME}"
ARCHIVE_PATH="${BUILD_PATH}/archive"
PHP_BUILD="${SOURCE_PATH}/build.php"

DOC_PATH="${PACKAGE_PATH}/docs/en"
COPY_FILES=(
    ["README.md"]="${DOC_PATH}"
    ["INSTALL-2.0.md"]="${PACKAGE_PATH}"
    ["INSTALL-2.0.md"]="${DOC_PATH}"
    ["UPGRADE-2.0.md"]="${PACKAGE_PATH}"
    ["UPGRADE-2.0.md"]="${DOC_PATH}"
    ["CHANGELOG-2.0.md"]="${DOC_PATH}"
    ["CHANGELOG-3.0.md"]="${DOC_PATH}"
    ["ROADMAP.md"]="${DOC_PATH}"
    ["composer.json"]="${DOC_PATH}/dev"
    ["composer.lock"]="${DOC_PATH}/dev"
)

echo "Prepare"
mkdir -p "${BUILD_PATH}"
mkdir -p "${EXPORT_PATH}"
mkdir -p "${PACKAGE_PATH}"
mkdir -p "${ARCHIVE_PATH}"

echo "Composer Install"
composer install --no-progress --no-suggest --prefer-dist --optimize-autoloader --no-scripts
composer run-script post-autoload-dump
composer run-script post-install-cmd

echo "Copying sources to package directory..."
cp -a "${SOURCE_PATH}/src/". "${PACKAGE_PATH}"

echo "Generating composer_vendors file..."
${PHP_BUILD} build:generate_vendor_doc --write-to "${PACKAGE_PATH}/docs/Composer_Vendors.md"

echo "Generating LESS file..."
${PHP_BUILD} build:generate_less --write-to "${PACKAGE_PATH}/web/bootstrap-font-awesome.css"

echo "Copying docs and composer files..."
mkdir -p "${DOC_PATH}/dev"
for fileName in "${!COPY_FILES[@]}"; do
    folder=${COPY_FILES[$fileName]}
    if [ -e "${SOURCE_PATH}/${fileName}" ]; then
        cp -f "${SOURCE_PATH}/${fileName}" "${folder}/"
    fi
done

echo "Purging tests from vendors..."
${PHP_BUILD} build:purge_vendors "${PACKAGE_PATH}/vendor"
echo "Fixing autoloader paths..."
${PHP_BUILD} build:fix_autoloader "${PACKAGE_PATH}/vendor"

echo "Creating translation files..."
php -dmemory_limit=2G "${PACKAGE_PATH}/bin/console" translation:extract template --output-format=po --output-dir="${PACKAGE_PATH}/app/Resources/translations" --enable-extractor=jms_i18n_routing --dir="${PACKAGE_PATH}/system" --dir="${PACKAGE_PATH}/lib/Zikula/Bundle"

echo "Clearing cache directory..."
mv "${PACKAGE_PATH}/var/cache/.htaccess" "${PACKAGE_PATH}/var/"
rm -rf "${PACKAGE_PATH}/var/cache/"*
mv "${PACKAGE_PATH}/var/.htaccess" "${PACKAGE_PATH}/var/cache/"

echo "Clearing log directory..."
if [ -e "${PACKAGE_PATH}/var/log" ]; then # Zikula 3+
    mv "${PACKAGE_PATH}/var/log/.htaccess" "${PACKAGE_PATH}/var/"
    rm -rf "${PACKAGE_PATH}/var/log/"*
    mv "${PACKAGE_PATH}/var/.htaccess" "${PACKAGE_PATH}/var/log/"
elif [ -e "${PACKAGE_PATH}/var/logs" ]; then # Zikula 2
    mv "${PACKAGE_PATH}/var/logs/.htaccess" "${PACKAGE_PATH}/var/"
    rm -rf "${PACKAGE_PATH}/var/logs/"*
    mv "${PACKAGE_PATH}/var/.htaccess" "${PACKAGE_PATH}/var/logs/"
fi

echo "Setting directory permissions..."
chmod -R 0777 "${PACKAGE_PATH}/app/config"
chmod -R 0777 "${PACKAGE_PATH}/app/config/dynamic"
chmod -R 0777 "${PACKAGE_PATH}/var/cache"
if [ -e "${PACKAGE_PATH}/var/log" ]; then # Zikula 3+
    chmod -R 0777 "${PACKAGE_PATH}/var/log"
elif [ -e "${PACKAGE_PATH}/var/logs" ]; then # Zikula 2
    chmod -R 0777 "${PACKAGE_PATH}/var/logs"
fi

echo "Creating archives..."
ARCHIVE_BASE_PATH="${ARCHIVE_PATH}/${BRANCH_NAME}"
cd "${EXPORT_PATH}"; zip -q -D -r "${ARCHIVE_BASE_PATH}.zip" .
cd "${EXPORT_PATH}"; tar cp "${BRANCH_NAME}" > "${ARCHIVE_BASE_PATH}.tar"; gzip "${ARCHIVE_BASE_PATH}.tar"

echo "Creating MD5 and SHA1 checksums..."
CHECKSUM_PATH="${ARCHIVE_PATH}/${BRANCH_NAME}-checksums"
TMP_FILE="${CHECKSUM_PATH}.tmp"
echo "-----------------md5sums-----------------" > "${TMP_FILE}"
md5sum "${ARCHIVE_PATH}/"*.tar.gz "${ARCHIVE_PATH}/"*.zip >> "${TMP_FILE}"
echo "-----------------sha1sums-----------------" >> "${TMP_FILE}"
sha1sum "${ARCHIVE_PATH}/"*.tar.gz "${ARCHIVE_PATH}/"*.zip >> "${TMP_FILE}"

cat "${TMP_FILE}" | sed "s!${ARCHIVE_PATH}/!!g" > "${CHECKSUM_PATH}.txt"
rm -f "${TMP_FILE}"
