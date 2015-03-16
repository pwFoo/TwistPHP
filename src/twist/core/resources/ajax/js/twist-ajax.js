/**
 * This file is part of TwistPHP.
 *
 * TwistPHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TwistPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
 * @link       https://twistphp.com
 *
 * Twist AJAX
 * --------------
 * @version 3.0.0
 */
try {
	if( $ ) {
		(
			function( window, document, $ ) {
				var contains = function( strNeedle, strHaystack, blCaseSensitive ) {
						blCaseSensitive = ( typeof blCaseSensitive === 'boolean' ) ? blCaseSensitive : false;
						if( blCaseSensitive ) {
							return strHaystack.indexOf( strNeedle ) !== -1;
						} else {
							return strHaystack.toLowerCase().indexOf( strNeedle.toLowerCase() ) !== -1;
						}
					},
				isBlank = function( mxdValue ) {
						return mxdValue.replace( /[\s\t\r\n]*/g, '' ) == '';
					},
				ie = function( mxdVersion ) {
						return navigator.userAgent.indexOf( 'MSIE ' );
					}
				isNull = function( mxdValue ) {
						return typeof( mxdValue ) === 'null' || mxdValue === null;
					},
				isSet = function( mxdValue ) {
						return mxdValue !== null && mxdValue !== undefined && typeof mxdValue !== 'null' && typeof mxdValue !== 'undefined' && mxdValue !== 'undefined';
					},
				log = function() {
						var arrArguements = arguments;
						if( isSet( window.console )
								&& isSet( window.console.log )
								&& arrArguements.length > 0 ) {
							for( var intArguement in arrArguements ) {
								window.console.log( arrArguements[intArguement] );
							}
						}
					},
				objectLength = function( objIn ) {
						var intLength = 0;
						if( typeof objIn === 'object' ) {
							for( var mxdKey in objIn ) {
								if( ( ie()
										&& Object.prototype.hasOwnProperty.call( objIn, mxdKey ) )
										|| objIn.hasOwnProperty( mxdKey ) ) {
									intLength++;
								}
							}
						}
						return intLength;
					},
				prettySize = function( intBytes, blUseSpace ) {
						var arrLimits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
						var intLimit = 0;
						while( arrLimits[intLimit]
								&& intBytes > Math.pow( 1024, intLimit + 1 ) ) {
							intLimit++;
						}
						return round( intBytes / Math.pow( 1024, intLimit ), 2 ) + ( typeof blUseSpace === 'boolean' && blUseSpace ? ' ' : '' ) + arrLimits[intLimit];
					},
				round = function( intNumber, intDP ) {
						intDP = ( typeof intDP !== 'number' ) ? 0 : intDP;
						return intDP === 0 ? parseInt( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) ) : parseFloat( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) );
					};

				var TwistAJAX = function( strAJAXPostLocation, b, c, d, e, f ) {
						var funMasterCallbackSuccess = function() {},
						funMasterCallbackFailure = function() {},
						objDefaultData = {},
						intMasterTimeout = 10000,
						strLoaderSize = 'medium',
						objRequests = {};

						if( typeof strAJAXPostLocation !== 'string'
								|| strAJAXPostLocation == '' ) {
							throw new Error( 'Need to specify a valid AJAX post location' );
						}

						if( typeof b === 'function' ) {
							funMasterCallbackSuccess = b;
							if( typeof c === 'function' ) {
								funMasterCallbackFailure = c;
								if( typeof d === 'object' ) {
									objDefaultData = d;
									if( typeof e === 'number' ) {
										intMasterTimeout = e;
										if( typeof f === 'string' ) {
											strLoaderSize = f;
										}
									} else if( typeof e === 'string' ) {
										strLoaderSize = e;
									}
								} else if( typeof d === 'number' ) {
									intMasterTimeout = d;
									if( typeof e === 'string' ) {
										strLoaderSize = e;
									}
								} else if( typeof d === 'string' ) {
									strLoaderSize = d;
								}
							} else if( c === 'object' ) {
								objDefaultData = c;
								if( typeof d === 'number' ) {
									intMasterTimeout = d;
									if( typeof e === 'string' ) {
										strLoaderSize = e;
									}
								} else if( typeof d === 'string' ) {
									strLoaderSize = d;
								}
							} else if( c === 'number' ) {
								intMasterTimeout = c;
								if( typeof d === 'string' ) {
									strLoaderSize = d;
								}
							} else if( typeof c === 'string' ) {
								strLoaderSize = c;
							}
						} else if( typeof b === 'object' ) {
							objDefaultData = b;
							if( typeof c === 'number' ) {
								intMasterTimeout = c;
								if( typeof d === 'string' ) {
									strLoaderSize = d;
								}
							} else if( typeof c === 'string' ) {
								strLoaderSize = c;
							}
						} else if( typeof b === 'number' ) {
							intMasterTimeout = b;
							if( typeof c === 'string' ) {
								strLoaderSize = c;
							}
						} else if( typeof b === 'string' ) {
							strLoaderSize = b;
						}

						$.ajaxSetup(
							{
								url: strAJAXPostLocation,
								timeout: intMasterTimeout
							}
						);

						var thisTwistAJAX = this;

						this.count = 0,
						this.defaultArray = objDefaultData,
						this.disableLoader = function() {
								thisTwistAJAX.showLoader = false;
								return thisTwistAJAX;
							},
						this.enableLoader = function() {
								thisTwistAJAX.showLoader = true;
								return thisTwistAJAX;
							},
						this.loaderSize = function( strSize ) {
								if( $( '#twist-ajax-loader' ).length ) {
									$( '#twist-ajax-loader' ).attr( 'class', '' ).addClass( strSize );
								}

								strLoaderSize = strSize;

								return thisTwistAJAX;
							},
						this.onfail = function( strMessage, strBugReportLink ) {
								var strErrorMessage = ( typeof strMessage === 'string' && strMessage !== '' ) ? strMessage : 'An unexpected AJAX response was given';
								log( strErrorMessage );

								return thisTwistAJAX;
							},
						this.send = function( strFunction, b, c, d, e ) {
								thisTwistAJAX.count++;
								$( '#twist-ajax-loader-size' ).text( 'Loading...' );
								if( thisTwistAJAX.count > 1 ) {
									$( '#twist-ajax-loader-count' ).text( thisTwistAJAX.count );
								}
								if( thisTwistAJAX.showLoader ) {
									$( '#twist-ajax-loader' ).stop().show().fadeTo( 0, 1 );
								}

								var objData = {
										foo: 'bar'
									},
								intTimeout = intMasterTimeout,
								funCallbackSuccess = function() {},
								funCallbackFailure = function() {};

								if( typeof b === 'object'
										|| ( typeof b === 'string'
											&& ( /^\#[0-9a-z_\-]+$/i ).test( b )
											&& ( $( b ) instanceof jQuery
												|| b.jquery ) ) ) {
									if( typeof b === 'object' ) {
										if( b instanceof jQuery
												|| b.jquery ) {
											objData = thisTwistAJAX.serializeJSON( b );
										} else {
											objData = ( objectLength( b ) === 0 ) ? objData : b;
										}
									} else {
										objData = thisTwistAJAX.serializeJSON( $( b ) );
									}
									if( typeof c === 'number' ) {
										intTimeout = c;
										if( typeof d === 'function' ) {
											funCallbackSuccess = d;
											if( typeof e === 'function' ) {
												funCallbackFailure = e;
											}
										}
									} else if( typeof c === 'function' ) {
										funCallbackSuccess = c;
										if( typeof d === 'function' ) {
											funCallbackFailure = d;
										}
									}
								} else if( typeof b === 'number' ) {
									intTimeout = b;
									if( typeof c === 'function' ) {
										funCallbackSuccess = c;
										if( typeof d === 'function' ) {
											funCallbackFailure = d;
										}
									}
								} else if( typeof b === 'function' ) {
									funCallbackSuccess = b;
									if( typeof c === 'function' ) {
										funCallbackFailure = c;
									}
								}

								var funCallbackSuccessEnd = function( objResponse ) {
										try {
											funCallbackSuccess.call( objResponse );
											funMasterCallbackSuccess.call( objResponse );
										} catch( err ) {
											log( err );
										}
									},
								funCallbackFailureEnd = function( objResponse ) {
										try {
											funCallbackFailure.call( objResponse );
											funMasterCallbackFailure.call( objResponse );
										} catch( err ) {
											log( err );
										}
									};

								var objPostData = {
										'function': strFunction,
										data: objData
									};

								$.each( thisTwistAJAX.defaultArray,
									function( strIndex, mxdValue ) {
										objPostData[strIndex] = mxdValue;
									}
								);

								var strUID = ( new Date() ).getTime(),
								xhrThis = $.ajax(
									{
										type: 'POST',
										url: strAJAXPostLocation,
										data: objPostData,
										dataType: 'json',
										timeout: intTimeout,
										global: true,
										cache: false,
										complete: function( jqXHR, strStatusText ) {
												thisTwistAJAX.count--;

												if( thisTwistAJAX.count === 0 ) {
													$( '#twist-ajax-loader' ).stop().fadeTo( 200, 0,
														function() {
															$( this ).hide();
														}
													);
												} else {
													$( '#twist-ajax-loader-count' ).text( thisTwistAJAX.count > 1 ? thisTwistAJAX.count : '' );
												}
											},
										success: function( objResponse, strStatusText, jqXHR ) {
												var strContentLength = prettySize( jqXHR.getResponseHeader( 'Content-Length' ) );
												$( '#twist-ajax-loader-size' ).text( 'Downloading ' + strContentLength + '...' );
												if( !isNull( objResponse )
														&& typeof objResponse === 'object'
														&& isSet( objResponse.status )
														&& objResponse.status === true ) {
													funCallbackSuccessEnd( objResponse );
												} else {
													funCallbackFailureEnd( objResponse );
												}
											},
										error: function( jqXHR, strStatusText, strError ) {
												switch( strStatusText ) {
													case 'abort':
														funCallbackFailureEnd();
														$( '#twist-ajax-loader-size' ).text( 'Aborted' );
														log( 'The AJAX request was aborted' );
													break;

													case 'timeout':
														funCallbackFailureEnd();
														$( '#twist-ajax-loader-size' ).text( 'Timeout' );
														log( 'The AJAX request timed out' );
													break;

													case 'parsererror':
														var rexJSON = /{"status":(true|false),"message":"[^"]*","title":"[^"]*","data":({.*}|\[\])(,"[^"]+":(true|false|("[^"]*")|({.*}|\[\])))*,"sticky":(true|false),"importance":\d+,"loggedin":(true|false),"login_redirect":(true|false),"output":"[^"]*"}/,
														strContentLength = prettySize( jqXHR.getResponseHeader( 'Content-Length' ) ),
														strSeperator = '==============================================================================================',
														strPostData = ( typeof JSON !== 'undefined' ) ? '\nPost              ' + JSON.stringify( objPostData ) : '';
														log( strSeperator + '\nDate              ' + jqXHR.getResponseHeader( 'Date' ) + '\nLocation          ' + strAJAXPostLocation + '\nFunction          ' + strFunction + '\nTimeout           ' + ( intTimeout / 1000 ) + 's\nResponse length   ' + strContentLength + strPostData + '\n' + strSeperator + '\n' + jqXHR.responseText.replace( rexJSON, '' ) );

														if( jqXHR.responseText.match( rexJSON ) ) {
															var strResponse = jqXHR.responseText.match( rexJSON )[0];
															if( $.parseJSON( strResponse ) !== null ) {
																var objResponse = $.parseJSON( strResponse );
																if( typeof objResponse === 'object'
																		&& 'status' in objResponse
																		&& objResponse.status === true ) {
																	funCallbackSuccessEnd( objResponse );
																} else {
																	funCallbackFailureEnd( objResponse );
																}
															} else {
																funCallbackFailureEnd();
																try {
																	thisTwistAJAX.onfail( strError );
																} catch( err ) {
																	log( err );
																}
															}
														} else {
															funCallbackFailureEnd();
															try {
																thisTwistAJAX.onfail( strError );
															} catch( err ) {
																log( err );
															}
														}
													break;

													case 'error':
													default:
														funCallbackFailureEnd();
														$( '#twist-ajax-loader-size' ).text( 'Error' );
														try {
															thisTwistAJAX.onfail( strError );
														} catch( err ) {
															log( err );
														}
													break;
												}
											}
									}
								);

								return thisTwistAJAX;
							},
						this.serializeJSON = function( jqoForm ) {
								var objJSON = {},
								arrFormElements = [];

								jQuery.map( jqoForm.serializeArray(),
									function( arrElement, intIndex ) {
										arrFormElements.push( { name: arrElement.name, value: arrElement.value } );
									}
								),
								jqoForm.find( 'input[type="submit"][name][value], input[type="reset"][name][value], input[type="button"][name][value], button[name][value]' ).each(
									function() {
										var jqoElement = $( this );
										arrFormElements.push( { name: jqoElement.attr( 'name' ), value: jqoElement.val() } );
									}
								);

								var returnNameObject = function( strFullName, strNameSoFar, strName, mxdValue ) {
										var objOut = {},
										arrNameMatches = strName.match( /^(\[([^\[]*)\])((\[[^\[]*\])*)$/i );

										if( arrNameMatches ) {
											var strThisKey = arrNameMatches[2];

											if( isBlank( strThisKey ) ) {
												var intKey = 1,
												blKeyExists = true;

												do {
													var blKeyFree = true;
													$.each( arrFormElements,
														function( intIndex, arrFormElement ) {
															if( contains( strNameSoFar + '[' + intKey + ']', arrFormElement.name ) ) {
																intKey++;
																blKeyFree = false;
															}
														}
													);

													if( blKeyFree ) {
														var blKeyReplaced = false;

														$.each( arrFormElements,
															function( intIndex, arrFormElement ) {
																if( !blKeyReplaced
																		&& arrFormElement.name === strNameSoFar + '[]'
																		&& arrFormElement.value === mxdValue ) {
																	arrFormElements[intIndex].name = strNameSoFar + '[' + intKey + ']';
																	blKeyReplaced = true;
																}
															}
														);

														blKeyExists = false;
													}
												} while( blKeyExists );

												strThisKey = intKey;
											}

											if( arrNameMatches[3] ) {
												objOut[strThisKey] = returnNameObject( strFullName, strNameSoFar + '[' + strThisKey + ']', arrNameMatches[3], mxdValue );
											} else {
												objOut[strThisKey] = mxdValue;
											}
										}

										return objOut;
									};

								$.each( arrFormElements,
									function( intIndex, arrFormElement ) {
										var arrNameMatches = arrFormElement.name.match( /^([^\[]+)((\[[^\[]*\])+)$/i );

										if( arrNameMatches ) {
											var objThisName = {};
											objThisName[arrNameMatches[1]] = returnNameObject( arrFormElement.name, arrNameMatches[1], arrNameMatches[2], arrFormElement.value );

											objJSON = $.extend( true, objJSON, objThisName );
										} else {
											objJSON[arrFormElement.name] = arrFormElement.value;
										}
									}
								);

								return objJSON;
							},
						this.showLoader = true;

						$( document ).ready(
							function() {
								$( 'body' ).prepend( '<div id="twist-ajax-loader" class="' + strLoaderSize + '"><span id="twist-ajax-loader-count"></span><span id="twist-ajax-loader-size"></span></div>' );
							}
						);

						window.TwistAJAXSessions.push( this );

						return true;
					};

				window.TwistAJAX = function( a, b, c, d ) {
						return new TwistAJAX( a, b, c, d );
					};

				if( !window.TwistAJAXSessions ) {
					window.TwistAJAXSessions = [];
				}
			}
		)( window, document, jQuery );
	} else {
		throw 'Twist AJAX requires jQuery to run';
	}
} catch( err ) {
	if( window.console
			&& window.console.log ) {
		console.log( err );
	}
}