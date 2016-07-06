<?php

namespace Honeybee\FrameworkBinding\Agavi\Validator;

use AgaviConfig;
use AgaviImageFileValidator;
use AgaviRequestDataHolder;
use AgaviUploadedFile;
use AgaviValidator;
use AgaviVirtualArrayPath;
use Honeybee\Common\Error\Error;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\FrameworkBinding\Agavi\Logging\LogTrait;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\HandlesFileInterface;
use Trellis\Runtime\Attribute\HandlesFileListInterface;
use Trellis\Runtime\Attribute\Image\Image;
use Trellis\Runtime\Validator\Result\IncidentInterface;
use Trellis\Runtime\Validator\Rule\Type\SanitizedFilenameRule;
use Honeybee\FrameworkBinding\Agavi\Request\HoneybeeUploadedFile;

class AggregateRootTypeFileUploadValidator extends AgaviValidator
{
    use LogTrait;

    protected $aggregate_root_type;

    protected $filetype_validator_implementor_map = [
        HandlesFileInterface::FILETYPE_FILE => 'AgaviFileValidator',
        HandlesFileInterface::FILETYPE_IMAGE => 'AgaviImageFileValidator',
        HandlesFileInterface::FILETYPE_VIDEO => 'AgaviFileValidator',
    ];

    protected function validate()
    {
        if ($this->hasMultipleArguments()) {
            throw new Error('Only a single argument is supported on this validator.');
        }

        // get all files from request for the current argument base
        $files =& $this->getData($this->getArgument());

        // no files submitted
        if (empty($files) || !is_array($files)) {
            $this->throwError('no_files');
            return false;
        }

        if (count($files) > 1) {
            $this->throwError('multiple_files');
            return false;
        }

        $attribute_path = null;
        $uploaded_file = null;

        foreach ($files as $name => $file) {
            $attribute_path = $name;
            $uploaded_file = $file;
        }

        $art = $this->getAggregateRootType();
/*
        $allowed = array_keys($art->getFileHandlingAttributes());
        if (!in_array($attribute_path, $allowed, true)) {
            $this->logDebug('Attempted file upload for unknown attribute', $attribute_path, 'in allowed', $allowed);
            $this->throwError('unknown_attribute');
            return false;
        }
*/
        $attribute = null;
        try {
            $attribute = $art->getAttribute($attribute_path);
        } catch (Exception $e) {
            $this->logInfo(
                'Attribute path specified for AggregateRootType',
                $art->getName(),
                'does not exist:',
                $attribute_path
            );
            $this->throwError('invalid_attribute_path');
            return false;
        }

        if (!$attribute instanceof AttributeInterface) {
            $this->logError('Attribute returned from AggregateRootType does not implement AttributeInterface');
            $this->throwError('unknown_attribute_implementation');
            return false;
        }

        //if ($uploaded_file->getError() === UPLOAD_ERR_OK) {}

        $path = new AgaviVirtualArrayPath($attribute_path);
        $success = $this->validateFileForAttribute($uploaded_file, $attribute, $path);
        if (!$success) {
            $this->throwError();
            return false;
        }

        $fss = $this->getServiceLocator()->getFilesystemService();

        $extension = $fss->guessExtensionForLocalFile(
            $uploaded_file->getTmpName(),
            $this->getParameter('fallback_extension', '')
        );

        // create an unique identifier usable as a relative location for the file (on filesystems; in databases)
        $file_identifier = $fss->generatePath(
            $attribute,
            $this->getParameter('generated_path_prefix', AgaviConfig::get('core.app_prefix', '')),
            $extension
        );

        // create a URI for a AR specific temporary target location with a relative filename
        // e.g. usertempfiles://user/image/random/uuid.jpg
        $target_tempfile_uri = $fss->createTempUri($file_identifier, $this->getAggregateRootType());

        //$this->logDebug(
        //    sprintf(
        //        'Stream copying "%s" to %s specific temporary location: %s',
        //        $uploaded_file->getTmpName(),
        //        $this->getAggregateRootType()->getName(),
        //        $target_tempfile_uri
        //    )
        //);

        // get a stream for the actually uploaded and validated file (probably from /tmp/)
        $uploaded_file_stream = $uploaded_file->getStream($this->getParameter('stream_read_mode', 'rb'));
        if (false === $uploaded_file_stream) {
            throw new RuntimeError('Could not open read stream to uploaded file: ', $uploaded_file->getTmpName());
        }

        /*
        $user_provided_original_filename = $uploaded_file->getName();
        // TODO this needs to be differen for HandlesFileInterface and HandlesFileListInterface
        $payload = [
            [
                $attribute->getFileLocationPropertyName() => $file_identifier,
                $attribute->getFileNamePropertyName() => $user_provided_original_filename
            ]
        ];
        $value_holder = $attribute->createValueHolder();
        $result = $value_holder->setValue($payload);
        if ($result->getSeverity() <= IncidentInterface::NOTICE) {
            // validation succeeded => use sanitized user provided filename
            // TODO assumption getFilename exists on valueobject => should perhaps be get(VO::PROPERTY_FILENAME)?
            // TODO assumption that validation fails due to filename, while it could be another rule failing
            // TODO using SanitizingFilenameRule directly may be used here, but could have different rules from
            //      the attribute in question here and would then lead to failing store later on in web form
            //      => can we get the defined filename rule and its options from the attribute?
            //      another option would be to use e.g. DOMPurifier clientside to prevent attack surface in form
            //      prior to submitting the uploaded file metadata as ajax is being used instead of twig escaping
            $uploaded_file->setFilename($value_holder->getValue()[0]->getFilename());
        } else {
            $uploaded_file->setFilename('');
        }
        */

        // image attribute => determine image dimensions and add it to the uploaded file's properties
        $image_width = $uploaded_file->getWidth();
        $image_height = $uploaded_file->getHeight();
        if ($attribute instanceof HandlesFileInterface &&
            $attribute->getFiletypeName() === HandlesFileInterface::FILETYPE_IMAGE
        ) {
            // as this may silently fail now the resulting image value object will have zero width/height
            $info = @getimagesize($uploaded_file->getTmpName());
            if ($info !== false) {
                $image_width = $info[0];
                $image_height = $info[1];
            }
        }

        // write uploaded file into temporary target location of that aggregate root type for later move into
        // the actual file storage of that aggregate root type (only move the file to the main storage when
        // the actual AggregateRootCommand was successfully dispatched)
        $success = $fss->writeStream($target_tempfile_uri, $uploaded_file_stream);
        if (!$success) {
            throw new RuntimeError(
                'Writing stream from uploaded file ' . $uploaded_file->getTmpName() .
                ' to temp storage ' . $target_tempfile_uri . ' failed.'
            );
        }

        $uploaded_file = $uploaded_file->createCopyWith([
            HoneybeeUploadedFile::PROPERTY_FILENAME => $this->getSanitizedFilename($uploaded_file->getName()),
            HoneybeeUploadedFile::PROPERTY_LOCATION => $file_identifier,
            HoneybeeUploadedFile::PROPERTY_MIMETYPE => $fss->getMimetype($target_tempfile_uri),
            HoneybeeUploadedFile::PROPERTY_FILESIZE => $fss->getSize($target_tempfile_uri),
            HoneybeeUploadedFile::PROPERTY_EXTENSION => $extension,
            HoneybeeUploadedFile::PROPERTY_WIDTH => $image_width,
            HoneybeeUploadedFile::PROPERTY_HEIGHT => $image_height
        ]);

        // Reset the uploaded file by reference
        $files[$attribute_path] = $uploaded_file;

        $this->setParameter('export_to_source', AgaviRequestDataHolder::SOURCE_PARAMETERS);
        $this->export($attribute, 'attribute');
        // $this->export($uploaded_file, $this->getBase() . '[%1$s]');

        // $this->logDebug('VALIDATION OF FILES:', $success ? 'SUCCESSFUL! \o/' : 'FAILED m(');

        return $success;
    }

    protected function validateFileForAttribute(
        AgaviUploadedFile $uploaded_file,
        AttributeInterface $attribute,
        AgaviVirtualArrayPath $path
    ) {
        // $this->logDebug('Delegating validation for path', $path, 'of entity type', $attribute->getType()->getName());

        $file_validator = $this->createFileValidatorForAttribute($attribute, $path);
        $file_validator->setParentContainer($this->getParentContainer());

        $result = $file_validator->execute($this->validationParameters);
        // $this->logDebug('argument name of filevalidator:', $file_validator->getArgument(), 'result:', $result);
        if ($result === AgaviValidator::NOT_PROCESSED) {
            $this->logDebug('validator for path', $path, 'was not processed');
            return false;
        }

        if ($result > AgaviValidator::SILENT) {
            //$this->throwErrorInParent($index=null, $affected_arg=null, $arg_relative=false, $set_affected=false)
            $this->logDebug('VALIDATION FAILED FOR FILE:', $file_validator->getArgument());
            foreach ($this->getParentContainer()->getErrorMessages() as $error_message) {
                $this->logDebug('error', $error_message);
            }
            //$this->throwError($file_validator->getArgument());
            return false;
        }

        // $this->logDebug('YEH FOR FILE:', $file_validator->getArgument(), 'attribute-name='.$attribute->getName());

        return true;
    }

    protected function createFileValidatorForAttribute(AttributeInterface $attribute, AgaviVirtualArrayPath $path)
    {
        $implementor = $this->getFileValidatorImplementor($attribute);

        $validator = new $implementor();

        $parts = $path->getParts();

        $new_path = clone $path;

        $argument_path = clone $path;
        $argument_name = $argument_path->pop();

        // $this->logDebug($attribute->getType()->getName(), $attribute->getName(), $argument_name, $new_path);

        $validator_definition = array_merge([], $attribute->getOptions());

        // TODO get "mandatory" option from entity instead as only that know whether it's actually required?
        if ($attribute->hasOption('mandatory')) {
            $validator_definition['required'] = $attribute->getOption('mandatory');
        } else {
            $validator_definition['required'] = false;
        }

        $validator_definition['name'] = sprintf('_invalid_file_%s', $new_path->__toString());

        // TODO should be taken from this validator and forwarded to the type specific validator
        $errors = $this->errorMessages; //array('' => 'Given file is invalid.');

        // TODO subset of params per validator/file type?
        $params = $this->getParameters();
        unset($params['class']);
        $params = array_merge($params, $validator_definition);

        $validator->initialize($this->getContext(), $params, array($new_path->__toString()), $errors);

        return $validator;
    }

    protected function getFileValidatorImplementor(AttributeInterface $attribute)
    {
        $attribute_path = $attribute->getPath();
        $attribute_path_validator_parameter = 'validator_' . $attribute_path;

        $filetype = $attribute->getFiletypeName();
        $filetype_validator_parameter = $filetype . '_validator';

        $implementor = $this->getParameter($attribute_path_validator_parameter);
        if (empty($implementor)) {
            $implementor = $this->getParameter($filetype_validator_parameter);
        }

        if (empty($implementor)) {
            if (!array_key_exists($filetype, $this->filetype_validator_implementor_map)) {
                throw new RuntimeError(
                    sprintf(
                        'No default validator implementor found for filetype "%s". Use validator parameter ' .
                        '"%s" to specify a class name. A custom validator may be specified for the current ' .
                        'attribute via "%s" parameter. The pattern is "%s". Extending this validator and ' .
                        'adjusting the defaults is another option.',
                        $filetype,
                        $filetype_validator_parameter,
                        $attribute_path_validator_parameter,
                        'validator_[attribute.path]'
                    )
                );
            }

            $implementor = $this->filetype_validator_implementor_map[$filetype];
        }

        return $implementor;
    }

    protected function getSanitizedFilename($insecure_user_provided_filename)
    {
        $options = $this->getParameter('filename_sanitization_rule_options', []);
        $rule = new SanitizedFilenameRule('filename', $options);
        if ($rule->apply($insecure_user_provided_filename)) {
            return $rule->getSanitizedValue();
        }

        return '';
    }

    protected function getAggregateRootType()
    {
        if (!$this->aggregate_root_type) {
            if (!$this->hasParameter('aggregate_root_type')) {
                throw new RuntimeError('Missing required parameter "aggregate_root_type".');
            }

            $aggregate_root_type = $this->getParameter('aggregate_root_type');
            $this->aggregate_root_type = $this->getServiceLocator()->getAggregateRootTypeMap()->getItem($aggregate_root_type);
        }

        return $this->aggregate_root_type;
    }

    protected function getServiceLocator()
    {
        return $this->getContext()->getServiceLocator();
    }

    protected function checkAllArgumentsSet($throw_error = true)
    {
        return true; // always run this validator
    }
}
