/**
 * Example admin app.
 *
 * Demonstrates the full loop: React admin page -> REST API -> database,
 * against the ExampleItemsController / wpkernel_example_items table.
 * Replace this with your own plugin's admin UI; delete the PHP-side
 * example (includes/Examples/, the example migration) at the same time.
 */
import {
	createRoot,
	useState,
	useEffect,
	useCallback,
} from '@wordpress/element';
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	TextControl,
	Notice,
	Spinner,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const REST_NAMESPACE = '/wpkernel/v1/items';

function ExampleApp() {
	const [ items, setItems ] = useState( [] );
	const [ title, setTitle ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ error, setError ] = useState( '' );

	const loadItems = useCallback( () => {
		setIsLoading( true );
		setError( '' );

		apiFetch( { path: REST_NAMESPACE } )
			.then( ( response ) => setItems( response ) )
			.catch( ( err ) =>
				setError(
					err.message || __( 'Failed to load items.', 'wpkernel' )
				)
			)
			.finally( () => setIsLoading( false ) );
	}, [] );

	useEffect( () => {
		loadItems();
	}, [ loadItems ] );

	const createItem = ( event ) => {
		event.preventDefault();

		if ( '' === title.trim() ) {
			return;
		}

		setIsSaving( true );
		setError( '' );

		apiFetch( {
			path: REST_NAMESPACE,
			method: 'POST',
			data: { title },
		} )
			.then( () => {
				setTitle( '' );
				loadItems();
			} )
			.catch( ( err ) =>
				setError(
					err.message || __( 'Failed to create item.', 'wpkernel' )
				)
			)
			.finally( () => setIsSaving( false ) );
	};

	return (
		<Card>
			<CardHeader>
				{ __( 'WPKernel Example Items', 'wpkernel' ) }
			</CardHeader>
			<CardBody>
				{ error && (
					<Notice status="error" isDismissible={ false }>
						{ error }
					</Notice>
				) }

				<form
					onSubmit={ createItem }
					style={ {
						display: 'flex',
						gap: '8px',
						marginBottom: '16px',
					} }
				>
					<TextControl
						label={ __( 'New item title', 'wpkernel' ) }
						hideLabelFromVision
						placeholder={ __( 'New item title', 'wpkernel' ) }
						value={ title }
						onChange={ setTitle }
					/>
					<Button
						variant="primary"
						type="submit"
						isBusy={ isSaving }
						disabled={ isSaving }
					>
						{ __( 'Add item', 'wpkernel' ) }
					</Button>
				</form>

				{ isLoading ? (
					<Spinner />
				) : (
					<ul>
						{ items.map( ( item ) => (
							<li key={ item.id }>{ item.title }</li>
						) ) }
						{ 0 === items.length && (
							<li>{ __( 'No items yet.', 'wpkernel' ) }</li>
						) }
					</ul>
				) }
			</CardBody>
		</Card>
	);
}

const mountId = window.wpKernelAdmin?.mountId ?? 'wpkernel-example-root';
const root = document.getElementById( mountId );

if ( root ) {
	createRoot( root ).render( <ExampleApp /> );
}
