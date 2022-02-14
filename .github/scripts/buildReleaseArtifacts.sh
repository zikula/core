#!/bin/bash
set -e

SOURCE_PATH="${GITHUB_WORKSPACE}"
BUILD_PATH="${GITHUB_WORKSPACE}/build_work"
BRANCH_PREFIX="refs/heads/"
BRANCH_NAME=${GITHUB_REF#"$BRANCH_PREFIX"}
EXPORT_PATH="${BUILD_PATH}/export"
PACKAGE_PATH="${EXPORT_PATH}/zikula"
ARCHIVE_PATH="${BUILD_PATH}/archive"
PHP_BUILD="./build.php"

DOC_PATH="${PACKAGE_PATH}/docs/en"
declare -A COPY_FILES
COPY_FILES=(
    ["README_md"]="${DOC_PATH}"
    ["CHANGELOG-2_0_md"]="${DOC_PATH}"
    ["CHANGELOG-3_0_md"]="${DOC_PATH}"
    ["composer_json"]="${DOC_PATH}/dev"
    ["composer_lock"]="${DOC_PATH}/dev"
)

echo "Create required directories..."
if [ "$BRANCH_NAME" = "2.0" ]; then # Zikula 2
    echo "Copying sources to package directory..."
    cp -r "${SOURCE_PATH}/src/" "${PACKAGE_PATH}"
else # Zikula 3
    echo "Copying sources to package directory..."
    # prevent copying sub directory into itself
    cp -r . /tmp/ZKTEMP
    mkdir -p "${PACKAGE_PATH}" "${ARCHIVE_PATH}"
    # exclude . and ..
    mv /tmp/ZKTEMP/{*,.[^.]*} "${PACKAGE_PATH}"
fi

cd "${PACKAGE_PATH}"

echo "Composer Install"
composer install --no-progress --prefer-dist --optimize-autoloader --no-scripts
echo "Post autoload dump"
composer run-script post-autoload-dump
echo "Post install command"
composer run-script post-install-cmd

echo "Generating composer_vendors file..."
${PHP_BUILD} build:generate_vendor_doc --write-to "${PACKAGE_PATH}/docs/General/VendorInformation.md"

echo "Copying docs and composer files..."
mkdir -p "${DOC_PATH}/dev"
for fileName in "${!COPY_FILES[@]}"; do
    FILE_NAME="${fileName//_/.}"
    FILE_FOLDER=${COPY_FILES[$fileName]}
    #echo "File: ${FILE_NAME}"
    #echo "Folder: ${FILE_FOLDER}"
    if [ -e "${SOURCE_PATH}/${FILE_NAME}" ]; then
        cp -f "${SOURCE_PATH}/${FILE_NAME}" "${FILE_FOLDER}/"
    fi
done

echo "Purging tests from vendors..."
${PHP_BUILD} build:purge_vendors "${PACKAGE_PATH}/vendor"

echo "Creating translation files..."
if [ "$BRANCH_NAME" = "2.0" ]; then # Zikula 2
    php -dmemory_limit=2G "${PACKAGE_PATH}/bin/console" translation:extract template --output-format=po --output-dir="${PACKAGE_PATH}/app/Resources/translations" --enable-extractor=jms_i18n_routing --dir="${PACKAGE_PATH}/system" --dir="${PACKAGE_PATH}/lib/Zikula/Bundle"
#else # Zikula 3
    #php bin/console zikula:translation:updateconfig
    #cd var/cache
    #rm -rf dev prod
    #cd ../..
    #php -dmemory_limit=2G "${PACKAGE_PATH}/bin/console" translation:extract zikula en
    #php -dmemory_limit=2G "${PACKAGE_PATH}/bin/console" zikula:translation:keytovalue
fi

echo "Clearing cache directory..."
mv "${PACKAGE_PATH}/var/cache/.htaccess" "${PACKAGE_PATH}/var/"
rm -rf "${PACKAGE_PATH}/var/cache/"*
mv "${PACKAGE_PATH}/var/.htaccess" "${PACKAGE_PATH}/var/cache/"

echo "Clearing log directory..."
if [ -e "${PACKAGE_PATH}/var/log" ]; then # Zikula 3+
#    mv "${PACKAGE_PATH}/var/log/.htaccess" "${PACKAGE_PATH}/var/"
    rm -rf "${PACKAGE_PATH}/var/log/"*
#    mv "${PACKAGE_PATH}/var/.htaccess" "${PACKAGE_PATH}/var/log/"
else # Zikula 2
    mv "${PACKAGE_PATH}/var/logs/.htaccess" "${PACKAGE_PATH}/var/"
    rm -rf "${PACKAGE_PATH}/var/logs/"*
    mv "${PACKAGE_PATH}/var/.htaccess" "${PACKAGE_PATH}/var/logs/"
fi

echo "Setting directory permissions..."
if [ -e "${PACKAGE_PATH}/var/log" ]; then # Zikula 3+
    chmod -R 0777 "${PACKAGE_PATH}/config"
    chmod -R 0777 "${PACKAGE_PATH}/config/dynamic"
    chmod -R 0777 "${PACKAGE_PATH}/var/cache"
    chmod -R 0777 "${PACKAGE_PATH}/var/log"
elif [ -e "${PACKAGE_PATH}/var/logs" ]; then # Zikula 2
    chmod -R 0777 "${PACKAGE_PATH}/app/config"
    chmod -R 0777 "${PACKAGE_PATH}/app/config/dynamic"
    chmod -R 0777 "${PACKAGE_PATH}/var/cache"
    chmod -R 0777 "${PACKAGE_PATH}/var/logs"
fi

rm -rf "${PACKAGE_PATH}/.git" "${PACKAGE_PATH}/.github"

echo "Creating archives..."
#if [ -e "${PACKAGE_PATH}/var/log" ]; then # Zikula 3+
#    ${PHP_BUILD} build:package --name="${BRANCH_NAME}" --build-dir="${ARCHIVE_PATH}" --source-dir="${PACKAGE_PATH}"
#else
    ARCHIVE_BASE_PATH="${ARCHIVE_PATH}/zikula"
    cd "${EXPORT_PATH}"; zip -q -D -r "${ARCHIVE_BASE_PATH}.zip" .
    cd "${EXPORT_PATH}"; tar cp "zikula" > "${ARCHIVE_BASE_PATH}.tar"; gzip "${ARCHIVE_BASE_PATH}.tar"

    echo "Creating MD5 and SHA1 checksums..."
    CHECKSUM_PATH="${ARCHIVE_PATH}/zikula-checksums"
    TMP_FILE="${CHECKSUM_PATH}.tmp"
    echo "-----------------md5sums-----------------" > "${TMP_FILE}"
    md5sum "${ARCHIVE_PATH}/"*.tar.gz "${ARCHIVE_PATH}/"*.zip >> "${TMP_FILE}"
    echo "-----------------sha1sums-----------------" >> "${TMP_FILE}"
    sha1sum "${ARCHIVE_PATH}/"*.tar.gz "${ARCHIVE_PATH}/"*.zip >> "${TMP_FILE}"

    cat "${TMP_FILE}" | sed "s!${ARCHIVE_PATH}/!!g" > "${CHECKSUM_PATH}.txt"
    rm -f "${TMP_FILE}"
#fi
