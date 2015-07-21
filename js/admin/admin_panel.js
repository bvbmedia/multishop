function renderAdminMenu(json, type, includeDescinFooter) {
    var footerDesc = includeDescinFooter;
    var admin_content = '';

    if (type!='newheader') {
        if (json != undefined && json.label) {
            var tablevel1 = json;

            admin_content += '<li>';

            if (tablevel1.label == null && tablevel1.description) {
                admin_content += tablevel1.description;

            } else {
                if (tablevel1.subs == null) {
                    if (tablevel1.link != null) {
                        admin_content += '<a href="' + tablevel1.link + '"' + (tablevel1.link_params != undefined ? tablevel1.link_params : '') + '>' + tablevel1.label + '</a>';
                    } else {
                        admin_content += tablevel1.label;
                    }

                } else {
                    var tablevel2_ctr = 0;
                    jQuery.each(tablevel1.subs, function (_tablevel2_key, _tablevel2) {
                        if (_tablevel2_key != '') {
                            tablevel2_ctr++;
                        }
                    });

                    total_tablevel2 = tablevel2_ctr;
                    counter_tablevel2 = 0;

                    if (tablevel1.link) {
                        admin_content += '<a href="' + tablevel1.link + '"' + (tablevel1.link_params != undefined ? tablevel1.link_params : '') + '>' + tablevel1.label + '</a>';

                    } else {
                        admin_content += '<span>' + tablevel1.label + '</span>';
                    }

                    admin_content += '<ul>';

                    jQuery.each(tablevel1.subs, function (tablevel2_key, tablevel2) {
                        counter_tablevel2++;

                        if (counter_tablevel2 == total_tablevel2) {
                            var last_class = ' last';
                        } else {
                            var last_class = '';
                        }

                        if (type == 'header' && (counter_tablevel2 == total_tablevel2)) {
                            tablevel2_params = 'dropdown_bottom';

                        } else if (type == 'footer' && (counter_tablevel2 == 1)) {
                            tablevel2_params = 'dropdown_top';

                        } else {
                            tablevel2_params = '';
                        }

                        if (tablevel2.divider) {
                            admin_content += '<li class="ms_admin_divider"></li>';
                            return true; // continue;
                        }

                        if (tablevel2.subs == null) {
                            if (tablevel2.link) {
                                /*
                                if (tablevel2.description != null && footerDesc > 0) {
                                    admin_content += '<li class="' + tablevel2_params + last_class + '"><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description">' + tablevel2.description + '</span></a></li>';
                                } else {
                                    admin_content += '<li class="' + tablevel2_params + last_class + '"><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></a></li>';
                                }
                                */
                                admin_content += '<li class="' + tablevel2_params + last_class + '"><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></a></li>';
                            } else {
                                /*
                                if (tablevel2.description != null && footerDesc > 0) {
                                    admin_content += '<li class="' + tablevel2_params + last_class + '"><span>' + tablevel2.label + '<span class="ms_admin_menu_item_description">' + tablevel2.description + '</span></span></li>';
                                } else {
                                    admin_content += '<li class="' + tablevel2_params + last_class + '"><span>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></span></li>';
                                }
                                */
                                admin_content += '<li class="' + tablevel2_params + last_class + '"><span>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></span></li>';
                            }

                        } else {
                            admin_content += '<li class="' + (tablevel2_params != '' ? tablevel2_params + ' ' : '') + (last_class != '' ? last_class + ' ' : '') + ' ms_admin_has_subs">';
                            if (tablevel2.link) {
                                /*
                                if (tablevel2.description != null && footerDesc > 0) {
                                    admin_content += '<span><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '</a><span class="ms_admin_menu_item_description">' + tablevel2.description + '</span></span>';
                                } else {
                                    admin_content += '<span><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '</a><span class="ms_admin_menu_item_description"></span></span>';
                                }
                                */
                                admin_content += '<span><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '</a><span class="ms_admin_menu_item_description"></span></span>';
                            } else {
                                /*
                                if (tablevel2.description != null && footerDesc > 0) {
                                    admin_content += '<span>' + tablevel2.label + '<span class="ms_admin_menu_item_description">' + tablevel2.description + '</span></span>';
                                } else {
                                    admin_content += '<span>' + tablevel2.label + '</a><span class="ms_admin_menu_item_description"></span></span>';
                                }
                                */
                                admin_content += '<span>' + tablevel2.label + '</a><span class="ms_admin_menu_item_description"></span></span>';
                            }
                            admin_content += '<ul>';

                            var tablevel3_ctr = 0;
                            jQuery.each(tablevel2.subs, function (_tablevel3_key, _tablevel3) {
                                if (_tablevel3_key != '') {
                                    tablevel3_ctr++;
                                }
                            });

                            total_tablevel3 = tablevel3_ctr;
                            counter_tablevel3 = 0;

                            jQuery.each(tablevel2.subs, function (tablevel3_key, tablevel3) {
                                counter_tablevel3++;

                                if (counter_tablevel3 == total_tablevel3) {
                                    var last_class = ' last';
                                } else {
                                    var last_class = '';
                                }

                                if (type == 'header' && (counter_tablevel3 == total_tablevel3)) {
                                    tablevel3_params = 'dropdown_bottom';

                                } else if (type == 'footer' && (counter_tablevel3 == 1)) {
                                    tablevel3_params = 'dropdown_top';

                                } else {
                                    tablevel3_params = '';
                                }

                                if (tablevel3.divider) {
                                    admin_content += '<li class="ms_admin_divider"></li>';
                                    return true; // continue;
                                }

                                if (tablevel3.subs == null) {
                                    if (tablevel3.link) {
                                        /*
                                        if (tablevel3.description != null && footerDesc > 0) {
                                            admin_content += '<li class="' + tablevel3_params + last_class + '"><a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></a></li>';
                                        } else {
                                            admin_content += '<li class="' + tablevel3_params + last_class + '"><a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></a></li>';
                                        }
                                        */
                                        admin_content += '<li class="' + tablevel3_params + last_class + '"><a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></a></li>';
                                    } else {
                                        /*
                                        if (tablevel3.description != null && footerDesc > 0) {
                                            admin_content += '<li class="' + tablevel3_params + last_class + '"><span>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></span></li>';
                                        } else {
                                            admin_content += '<li class="' + tablevel3_params + last_class + '"><span>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></span></li>';
                                        }
                                        */
                                        admin_content += '<li class="' + tablevel3_params + last_class + '"><span>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></span></li>';
                                    }
                                } else {
                                    admin_content += '<li class="' + (tablevel3_params != '' ? tablevel3_params + ' ' : '') + (last_class != '' ? last_class + ' ' : '') + ' ms_admin_has_subs">';
                                    if (tablevel3.link) {
                                        /*
                                        if (tablevel3.description != null && footerDesc > 0) {
                                            admin_content += '<span><a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '</a><span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></span>';
                                        } else {
                                            admin_content += '<span><a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '</a><span class="ms_admin_menu_item_description"></span></span>';
                                        }
                                        */
                                        admin_content += '<span><a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '</a><span class="ms_admin_menu_item_description"></span></span>';
                                    } else {
                                        /*
                                        if (tablevel3.description != null && footerDesc > 0) {
                                            admin_content += '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></span>';
                                        } else {
                                            admin_content += '<span>' + tablevel3.label + '</a><span class="ms_admin_menu_item_description"></span></span>';
                                        }
                                        */
                                        admin_content += '<span>' + tablevel3.label + '</a><span class="ms_admin_menu_item_description"></span></span>';
                                    }
                                    admin_content += '<ul>';

                                    var tablevel4_ctr = 0;
                                    jQuery.each(tablevel3.subs, function (_tablevel4_key, _tablevel4) {
                                        if (_tablevel4_key != '') {
                                            tablevel4_ctr++;
                                        }
                                    });

                                    total_tablevel4 = tablevel4_ctr;
                                    counter_tablevel4 = 0;

                                    jQuery.each(tablevel3.subs, function (tablevel4_key, tablevel4) {
                                        counter_tablevel4++;

                                        if (type == 'header' && (counter_tablevel4 == total_tablevel4)) {
                                            tablevel4_params = 'dropdown_bottom';

                                        } else if (type == 'footer' && (counter_tablevel4 == 1)) {
                                            tablevel4_params = 'dropdown_top';

                                        } else {
                                            tablevel4_params = '';
                                        }

                                        if (counter_tablevel4 == total_tablevel4) {
                                            admin_content += '<li class="' + (tablevel4_key != '' ? tablevel4_key + ' ' : '') + 'last">';
                                        } else {
                                            admin_content += '<li class="' + tablevel4_key + '">';
                                        }

                                        if (tablevel4.link) {
                                            /*
                                            if (tablevel4.description != null && footerDesc > 0) {
                                                admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description">' + tablevel4.description + '</span></a>';
                                            } else {
                                                admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                            }
                                            */
                                            admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                        } else {
                                            /*
                                            if (tablevel4.description != null && footerDesc > 0) {
                                                admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description">' + tablevel4.description + '</span></span>';
                                            } else {
                                                admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                            }
                                            */
                                            admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                        }

                                        admin_content += '</li>';

                                    });

                                    admin_content += '</ul></li>';
                                }
                                /*if (counter_tablevel3 == total_tablevel3) {
                                 admin_content += '<li class="' + (tablevel3_key!='' ? tablevel3_key + ' ' : '') + 'last">';
                                 } else {
                                 admin_content += '<li class="' + tablevel3_key + '">';
                                 }

                                 if (tablevel3.link) {
                                 if (tablevel3.description != null && footerDesc > 0) {
                                 admin_content 	+= '<a href="' + tablevel3.link + '"' + (tablevel3.link_params!=undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></a>';
                                 } else {
                                 admin_content 	+= '<a href="' + tablevel3.link + '"' + (tablevel3.link_params!=undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                 }

                                 } else {
                                 if (tablevel3.description != null && footerDesc > 0) {
                                 admin_content 	+= '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></span>';
                                 } else {
                                 admin_content 	+= '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                 }
                                 }

                                 admin_content += '</li>';*/
                            });

                            admin_content += '</ul></li>';
                        }
                    });

                    admin_content += '</ul>';
                }
            }

            admin_content += '</li>';

        } else {

            var total_tabs = 0;
            jQuery.each(json, function (_tablevel0_key, _tablevel0) {
                if (_tablevel0_key != '') {
                    total_tabs++;
                }
            });

            var tab_counter = 0;
            jQuery.each(json, function (tablevel1_key, tablevel1) {
                tab_counter++;
                admin_content += '<li role="presentation" class="' + tablevel1_key + ' dropdown">';
                if (tablevel1.label == null && tablevel1.description) {
                    admin_content += tablevel1.description;

                } else {
                    if (tablevel1.subs == null) {
                        if (tablevel1.link != null) {
                            admin_content += '<a href="' + tablevel1.link + '"' + (tablevel1.link_params != undefined ? tablevel1.link_params : '') + '>';
                            if (tablevel1.class) {
                                admin_content += '<i class="' + tablevel1.class + '"></i>';
                            }
                            admin_content += tablevel1.label + '</a>';
                        } else {
                            admin_content += tablevel1.label;
                        }
                    } else {
                        var tablevel2_ctr = 0;
                        jQuery.each(tablevel1.subs, function (_tablevel2_key, _tablevel2) {
                            if (_tablevel2_key != '') {
                                tablevel2_ctr++;
                            }
                        });

                        total_tablevel2 = tablevel2_ctr;
                        counter_tablevel2 = 0;
                        /*
                        if (tablevel1.link) {
                            //admin_content += '<a href="' + tablevel1.link + '"' + (tablevel1.link_params != undefined ? tablevel1.link_params : '') + '>' + tablevel1.label + '</a>';
                            admin_content += '<a data-toggle="collapse" href="#subs' + tablevel1_key + '" aria-expanded="false" aria-controls="subs' + tablevel2_ctr + '">';
                            if (tablevel1.class) {
                                admin_content += '<i class="' + tablevel1.class + '"></i>';
                            }
                            admin_content += tablevel1.label+'</a>';

                        } else {
                            //admin_content += '<span>' + tablevel1.label + '</span>';
                            admin_content += '<a data-toggle="collapse" href="#subs' + tablevel1_key + '" aria-expanded="false" aria-controls="subs' + tablevel2_ctr + '">';
                            if (tablevel1.class) {
                                admin_content += '<i class="' + tablevel1.class + '"></i>';
                            }
                            admin_content += tablevel1.label+'</a>';
                        }
                        */
                        //<a data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" href="#subs' + tablevel1_key + '" aria-expanded="false" aria-controls="subs' + tablevel2_ctr + '">
                        admin_content += '<a href="#" role="button" id="subs' + tablevel1_key + '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                        if (tablevel1.class) {
                            admin_content += '<i class="' + tablevel1.class + '"></i>';
                        }
                        admin_content += tablevel1.label+'</a>';
                        admin_content += '<ul class="dropdown-menu">';

                        jQuery.each(tablevel1.subs, function (tablevel2_key, tablevel2) {
                            counter_tablevel2++;

                            if (type == 'header' && (counter_tablevel2 == total_tablevel2)) {
                                tablevel2_params = 'dropdown_bottom';

                            } else if (type == 'footer' && (counter_tablevel2 == 1)) {
                                tablevel2_params = 'dropdown_top';

                            } else {
                                tablevel2_params = '';
                            }

                            if (tablevel2.divider) {
                                admin_content += '<li class="ms_admin_divider"></li>';
                                return true; // continue;
                            }

                            if (tablevel2.subs == null) {
                                if (tablevel2.link) {
                                    /*
                                    if (tablevel2.description != null) {
                                        admin_content += '<li class="' + tablevel2_params + '"><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description">' + tablevel2.description + '</span></a></li>';
                                    } else {
                                        admin_content += '<li class="' + tablevel2_params + '"><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></a></li>';
                                    }
                                    */
                                    admin_content += '<li class="' + tablevel2_params + '"><a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></a></li>';
                                } else {
                                    /*
                                    if (tablevel2.description != null) {
                                        admin_content += '<li class="' + tablevel2_params + '"><span>' + tablevel2.label + '<span class="ms_admin_menu_item_description">' + tablevel2.description + '</span></span></li>';
                                    } else {
                                        admin_content += '<li class="' + tablevel2_params + '"><span>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></span></li>';
                                    }
                                    */
                                    admin_content += '<li class="' + tablevel2_params + '"><span>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></span></li>';
                                }

                            } else {

                                //admin_content += '<li class="' + (tablevel2_params != '' ? tablevel2_params + ' ' : '') + 'ms_admin_has_subs">';
                                /*
                                if (tablevel2.link) {
                                    *//*
                                    if (tablevel2.description != null) {
                                        admin_content += '<a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description">' + tablevel2.description + '</span></a>';
                                    } else {
                                        admin_content += '<a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                    }
                                    *//*
                                    admin_content += '<a href="' + tablevel2.link + '"' + (tablevel2.link_params != undefined ? tablevel2.link_params : '') + '>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                } else {
                                    *//*
                                    if (tablevel2.description != null) {
                                        admin_content += '<span>' + tablevel2.label + '<span class="ms_admin_menu_item_description">' + tablevel2.description + '</span></span>';
                                    } else {
                                        admin_content += '<span>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                    }
                                    *//*
                                    admin_content += '<span>' + tablevel2.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                }*/
                                admin_content += '<li><a href="#">';
                                if (tablevel2.class) {
                                    admin_content += '<i class="' + tablevel2.class + '"></i>';
                                }
                                admin_content += tablevel2.label+'</a>';
                                admin_content += '<ul class="dropdown-menu">';

                                //admin_content += '<ul>';

                                var tablevel3_ctr = 0;
                                jQuery.each(tablevel2.subs, function (_tablevel3_key, _tablevel3) {
                                    if (_tablevel3_key != '') {
                                        tablevel3_ctr++;
                                    }
                                });

                                total_tablevel3 = tablevel3_ctr;
                                counter_tablevel3 = 0;

                                jQuery.each(tablevel2.subs, function (tablevel3_key, tablevel3) {
                                    counter_tablevel3++;

                                    if (type == 'header' && (counter_tablevel3 == total_tablevel3)) {
                                        tablevel3_params = 'dropdown_bottom';

                                    } else if (type == 'footer' && (counter_tablevel3 == 1)) {
                                        tablevel3_params = 'dropdown_top';

                                    } else {
                                        tablevel3_params = '';
                                    }

                                    if (tablevel3.subs == null) {
                                        admin_content += '<li class="' + tablevel3_key + '">';
                                        if (tablevel3.link) {
                                            /*
                                            if (tablevel3.description != null) {
                                                admin_content += '<a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></a>';
                                            } else {
                                                admin_content += '<a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                            }
                                            */
                                            admin_content += '<a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                        } else {
                                            /*
                                            if (tablevel3.description != null) {
                                                admin_content += '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></span>';
                                            } else {
                                                admin_content += '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                            }
                                            */
                                            admin_content += '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                        }
                                        admin_content += '</li>';

                                    } else {
                                        admin_content += '<li class="' + (tablevel3_key != '' ? tablevel3_key + ' ' : '') + 'ms_admin_has_subs">';

                                        if (tablevel3.link) {
                                            /*
                                            if (tablevel3.description != null) {
                                                admin_content += '<a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></a>';
                                            } else {
                                                admin_content += '<a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                            }
                                            */
                                            admin_content += '<a href="' + tablevel3.link + '"' + (tablevel3.link_params != undefined ? tablevel3.link_params : '') + '>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></a>';

                                        } else {
                                            /*
                                            if (tablevel3.description != null) {
                                                admin_content += '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description">' + tablevel3.description + '</span></span>';
                                            } else {
                                                admin_content += '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                            }
                                            */
                                            admin_content += '<span>' + tablevel3.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                        }

                                        admin_content += '<ul>';

                                        var tablevel4_ctr = 0;
                                        jQuery.each(tablevel3.subs, function (_tablevel4_key, _tablevel4) {
                                            if (_tablevel4_key != '') {
                                                tablevel4_ctr++;
                                            }
                                        });

                                        total_tablevel4 = tablevel4_ctr;
                                        counter_tablevel4 = 0;

                                        jQuery.each(tablevel3.subs, function (tablevel4_key, tablevel4) {
                                            counter_tablevel4++;

                                            if (type == 'header' && (counter_tablevel4 == total_tablevel4)) {
                                                tablevel4_params = 'dropdown_bottom';

                                            } else if (type == 'footer' && (counter_tablevel4 == 1)) {
                                                tablevel4_params = 'dropdown_top';

                                            } else {
                                                tablevel4_params = '';
                                            }

                                            if (tablevel4.subs == null) {
                                                admin_content += '<li class="' + tablevel4_key + '">';

                                                if (tablevel4.link) {
                                                    /*
                                                    if (tablevel4.description != null) {
                                                        admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description">' + tablevel4.description + '</span></a>';
                                                    } else {
                                                        admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                                    }
                                                    */

                                                    admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                                } else {
                                                    /*
                                                    if (tablevel4.description != null) {
                                                        admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description">' + tablevel4.description + '</span></span>';
                                                    } else {
                                                        admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                                    }
                                                    */
                                                    admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                                }

                                                admin_content += '</li>';
                                            } else {

                                                admin_content += '<li class="' + tablevel4_key + '">';

                                                if (tablevel4.link) {
                                                    /*
                                                    if (tablevel4.description != null) {
                                                        admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description">' + tablevel4.description + '</span></a>';
                                                    } else {
                                                        admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                                    }
                                                    */
                                                    admin_content += '<a href="' + tablevel4.link + '"' + (tablevel4.link_params != undefined ? tablevel4.link_params : '') + '>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></a>';

                                                } else {
                                                    /*
                                                    if (tablevel4.description != null) {
                                                        admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description">' + tablevel4.description + '</span></span>';
                                                    } else {
                                                        admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                                    }
                                                    */
                                                    admin_content += '<span>' + tablevel4.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                                }

                                                admin_content += '<ul>';

                                                var tablevel5_ctr = 0;
                                                jQuery.each(tablevel4.subs, function (_tablevel5_key, _tablevel5) {
                                                    if (_tablevel5_key != '') {
                                                        tablevel5_ctr++;
                                                    }
                                                });

                                                total_tablevel5 = tablevel5_ctr;
                                                counter_tablevel5 = 0;

                                                jQuery.each(tablevel4.subs, function (tablevel5_key, tablevel5) {
                                                    counter_tablevel5++;

                                                    if (type == 'header' && (counter_tablevel5 == total_tablevel5)) {
                                                        tablevel5_params = 'dropdown_bottom';

                                                    } else if (type == 'footer' && (counter_tablevel5 == 1)) {
                                                        tablevel5_params = 'dropdown_top';

                                                    } else {
                                                        tablevel5_params = '';
                                                    }

                                                    admin_content += '<li class="' + tablevel5_key + '">';

                                                    if (tablevel5.link) {
                                                        /*
                                                        if (tablevel5.description != null) {
                                                            admin_content += '<a href="' + tablevel5.link + '"' + (tablevel5.link_params != undefined ? tablevel5.link_params : '') + '>' + tablevel5.label + '<span class="ms_admin_menu_item_description">' + tablevel5.description + '</span></a>';
                                                        } else {
                                                            admin_content += '<a href="' + tablevel5.link + '"' + (tablevel5.link_params != undefined ? tablevel5.link_params : '') + '>' + tablevel5.label + '<span class="ms_admin_menu_item_description"></span></a>';
                                                        }
                                                        */
                                                        admin_content += '<a href="' + tablevel5.link + '"' + (tablevel5.link_params != undefined ? tablevel5.link_params : '') + '>' + tablevel5.label + '<span class="ms_admin_menu_item_description"></span></a>';

                                                    } else {
                                                        /*(
                                                        if (tablevel5.description != null) {
                                                            admin_content += '<span>' + tablevel5.label + '<span class="ms_admin_menu_item_description">' + tablevel5.description + '</span></span>';
                                                        } else {
                                                            admin_content += '<span>' + tablevel5.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                                        }
                                                        */
                                                        admin_content += '<span>' + tablevel5.label + '<span class="ms_admin_menu_item_description"></span></span>';
                                                    }

                                                    admin_content += '</li>';

                                                });

                                                admin_content += '</ul></li>';

                                            }

                                        });

                                        admin_content += '</ul></li>';
                                    }
                                });
                                admin_content += '</ul></li>';
                            }
                        });
                        admin_content += '</ul>';
                    }
                }

                admin_content += '</li>';

            });
        }
    } else {
        var total_tabs = 0;
        jQuery.each(json, function (_tablevel0_key, _tablevel0) {
            if (_tablevel0_key != '') {
                total_tabs++;
            }
        });
        var tab_counter = 0;
        jQuery.each(json, function (tablevel1_key, tablevel1) {
            tab_counter++;
            if (tablevel1.label == null && tablevel1.description) {
                admin_content += tablevel1.description;

            } else {
                if (tablevel1.subs == null) {
                    if (tablevel1.link != null) {
                        admin_content += '<a href="' + tablevel1.link + '"' + (tablevel1.link_params != undefined ? tablevel1.link_params : '') + '>' + tablevel1.label + '</a>';
                    } else {
                        admin_content += tablevel1.label;
                    }
                }
            }
        });
    }
    return admin_content;
}
