<?php
// This file is generated. Do not modify it manually.
return array(
	'aside' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'portfolio-core/aside',
		'version' => '0.1.0',
		'title' => 'Aside',
		'category' => 'text',
		'icon' => 'info-outline',
		'description' => 'A short note set apart from the text, with a label: a side note, or a decision and why it was made.',
		'keywords' => array(
			'note',
			'decision',
			'callout',
			'aside'
		),
		'textdomain' => 'portfolio-core',
		'attributes' => array(
			'label' => array(
				'type' => 'string',
				'source' => 'text',
				'selector' => '.aside-a__label',
				'default' => ''
			)
		),
		'variations' => array(
			array(
				'name' => 'note',
				'title' => 'Note',
				'description' => 'A side note set apart from the text.',
				'attributes' => array(
					'label' => 'Note'
				),
				'isDefault' => true,
				'scope' => array(
					'inserter',
					'transform'
				),
				'isActive' => array(
					'label'
				)
			),
			array(
				'name' => 'decision',
				'title' => 'Decision',
				'description' => 'A decision and why it was made.',
				'icon' => 'yes-alt',
				'attributes' => array(
					'label' => 'Decision'
				),
				'scope' => array(
					'inserter',
					'transform'
				),
				'isActive' => array(
					'label'
				)
			)
		),
		'supports' => array(
			'className' => false,
			'customClassName' => false,
			'html' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'code' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'portfolio-core/code',
		'version' => '0.1.0',
		'title' => 'Code with file name',
		'category' => 'text',
		'icon' => 'editor-code',
		'description' => 'Code with an optional file name above it and an optional caption below.',
		'keywords' => array(
			'code',
			'snippet',
			'file',
			'pre'
		),
		'textdomain' => 'portfolio-core',
		'attributes' => array(
			'filename' => array(
				'type' => 'string',
				'source' => 'text',
				'selector' => '.code-a__filename',
				'default' => ''
			),
			'content' => array(
				'type' => 'string',
				'source' => 'text',
				'selector' => 'code',
				'default' => ''
			),
			'caption' => array(
				'type' => 'rich-text',
				'source' => 'rich-text',
				'selector' => 'figcaption'
			)
		),
		'supports' => array(
			'align' => array(
				'wide'
			),
			'className' => false,
			'customClassName' => false,
			'html' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'related' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'portfolio-core/related',
		'version' => '0.1.0',
		'title' => 'Related',
		'category' => 'widgets',
		'icon' => 'admin-links',
		'description' => 'Links the note to one service and, optionally, one project. Rendered from the current data, so titles and links stay up to date.',
		'keywords' => array(
			'service',
			'project',
			'work',
			'link'
		),
		'textdomain' => 'portfolio-core',
		'attributes' => array(
			'serviceId' => array(
				'type' => 'integer',
				'default' => 0
			),
			'projectId' => array(
				'type' => 'integer',
				'default' => 0
			)
		),
		'supports' => array(
			'className' => false,
			'customClassName' => false,
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);
