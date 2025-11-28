import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import {
	InspectorControls,
	BlockControls,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	TextControl,
	ToolbarGroup,
	ToolbarButton,
	SelectControl,
	ToggleControl,
	Panel,
	PanelBody,
} from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	function updateHeadline( newValue ) {
		setAttributes( { headline: newValue } );
	}

	function updateLevel( newValue ) {
		setAttributes( { level: newValue } );
	}

	function updateImagesize( newValue ) {
		setAttributes( {
			imgsize: newValue === '' ? 0 : parseInt( newValue, 10 ),
		} );
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<Panel>
					<PanelBody>
						<TextControl
							onChange={ updateHeadline }
							className="headline"
							label={ __( 'Headline', 'list-yarpp-block' ) }
							value={ attributes.headline }
							placeholder={ __(
								'Write headline',
								'list-yarpp-block'
							) }
						/>
						<TextControl
							onChange={ updateImagesize }
							className="imgsize"
							label={ __( 'Image size', 'list-yarpp-block' ) }
							type="number"
							value={ attributes.imgsize }
							placeholder={ __(
								'Image size',
								'list-yarpp-block'
							) }
						/>
						<SelectControl
							label={ __( 'Heading level', 'list-yarpp-block' ) }
							help={ __(
								'Select the heading level',
								'list-yarpp-block'
							) }
							value={ attributes.level }
							options={ [
								{
									value: 'h1',
									label: __( 'H1', 'list-yarpp-block' ),
								},
								{
									value: 'h2',
									label: __( 'H2', 'list-yarpp-block' ),
								},
								{
									value: 'h3',
									label: __( 'H3', 'list-yarpp-block' ),
								},
								{
									value: 'h4',
									label: __( 'H4', 'list-yarpp-block' ),
								},
								{
									value: 'h5',
									label: __( 'H5', 'list-yarpp-block' ),
								},
								{
									value: 'h6',
									label: __( 'H6', 'list-yarpp-block' ),
								},
							] }
							onChange={ updateLevel }
						/>
						<SelectControl
							label={ __(
								'YARPP block type',
								'list-yarpp-block'
							) }
							help={ __(
								'Displays 3 posts of the configured type.',
								'list-yarpp-block'
							) }
							value={ attributes.blocktype }
							options={ [
								{
									value: 'related',
									label: __(
										'Related posts (default)',
										'list-yarpp-block'
									),
								},
								{
									value: 'latest',
									label: __(
										'Latest posts excluding related posts',
										'list-yarpp-block'
									),
								},
							] }
							onChange={ ( blocktype ) =>
								setAttributes( { blocktype } )
							}
						/>
						<ToggleControl
							label={ __(
								'Open links in new tab.',
								'list-yarpp-block'
							) }
							help={ __(
								'Adds target="_blank" and rel="noopener" parameters to the links.',
								'list-yarpp-block'
							) }
							checked={ attributes.targetblank }
							onChange={ () =>
								setAttributes( {
									targetblank: ! attributes.targetblank,
								} )
							}
						/>
					</PanelBody>
				</Panel>
			</InspectorControls>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						label={ __( 'Update YARPP Block', 'list-yarpp-block' ) }
						onClick={ () =>
							setAttributes( { updated: Date.now() } )
						}
						icon="update"
					/>
				</ToolbarGroup>
			</BlockControls>
			<ServerSideRender
				block="yarpp-block/list"
				attributes={ attributes }
			/>
		</div>
	);
}
