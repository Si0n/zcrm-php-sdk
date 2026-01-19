<?php

return new PhpCsFixer\Config()
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setUnsupportedPhpVersionAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        '@PSR2' => false,
        'concat_space' => ['spacing' => 'one'],
        'function_typehint_space' => true,
        'array_indentation' => true,
        'indentation_type' => true,
        'phpdoc_align' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'single_blank_line_at_eof' => true,
        'single_space_after_construct' => true,
        'whitespace_after_comma_in_array' => true,
        // Modern rules
        'ordered_imports' => ['sort_algorithm' => 'alpha'], // Order imports alphabetically
        'phpdoc_order' => true, // Order PHPDoc tags
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'], // Place `null` type last in PHPDocs
        'no_unused_imports' => true, // Remove unused `use` statements
        'binary_operator_spaces' => true, // Align binary operators
        'ternary_to_null_coalescing' => true, // Use `??` instead of ternary with null
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true], // Remove unnecessary PHPDoc tags
        'fully_qualified_strict_types' => true, // Force FQCN in strict types
        'trailing_comma_in_multiline' => ['elements' => ['arrays']], // Add trailing commas in multiline arrays
        'class_attributes_separation' => ['elements' => ['method' => 'one']], // Separate methods with a blank line
        'single_trait_insert_per_statement' => true, // One trait per `use` statement
        'types_spaces' => ['space' => 'single'], // Single space in type declarations
        'no_useless_else' => true, // Remove unnecessary `else` clauses
        'no_useless_return' => true, // Remove unnecessary `return` statements
        'return_type_declaration' => ['space_before' => 'none'], // Standardize return type declarations
    ]);
