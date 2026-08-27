/**
 * jQuery MultiSelect Plugin for Widget Builder
 * Custom implementation for multi-board selection
 */
(function($) {
    $.fn.multiselect = function(options) {
        var defaults = {
            placeholder: 'Select options',
            selectAll: true,
            minHeight: 200,
            maxHeight: 300,
            showCheckbox: true,
            onLoad: function(element) {},
            onOptionClick: function(element, option) {},
            onSelectAll: function(element, checked) {}
        };
        
        var settings = $.extend({}, defaults, options);
        
        return this.each(function() {
            var $select = $(this);
            var $options = $select.find('option');
            var selectedCount = 0;
            
            // Hide original select
            $select.hide();
            
            // Remove existing wrapper if any
            if ($select.next('.ms-options-wrap').length) {
                $select.next('.ms-options-wrap').remove();
            }
            
            // Create wrapper
            var $wrapper = $('<div class="ms-options-wrap"></div>');
            
            // Create button
            var $button = $('<button type="button" class="ms-options"></button>');
            updateButtonText();
            
            // Create dropdown
            var $dropdown = $('<div class="ms-options-dropdown"></div>');
            var $list = $('<ul></ul>');
            
            // Add "Select All" option if enabled
            if (settings.selectAll && $options.length > 1) {
                var $selectAllLi = $('<li class="ms-select-all"></li>');
                var $selectAllLabel = $('<label></label>');
                var $selectAllCheckbox = $('<input type="checkbox" name="ms-select-all" />');
                $selectAllLabel.append($selectAllCheckbox);
                $selectAllLabel.append(' 전체 선택');
                $selectAllLi.append($selectAllLabel);
                $list.append($selectAllLi);
                
                // Select All functionality
                $selectAllCheckbox.on('change', function() {
                    var isChecked = $(this).prop('checked');
                    $list.find('li:not(.ms-select-all) input[type="checkbox"]').prop('checked', isChecked);
                    $select.find('option').prop('selected', isChecked);
                    updateButtonText();
                    settings.onSelectAll($select, isChecked);
                });
            }
            
            // Add options
            $options.each(function(index) {
                var $option = $(this);
                var value = $option.val();
                var text = $option.text();
                var isSelected = $option.prop('selected');
                
                var $li = $('<li></li>');
                var $label = $('<label></label>');
                var $checkbox = $('<input type="checkbox" />');
                
                $checkbox.attr('value', value);
                $checkbox.prop('checked', isSelected);
                
                $label.append($checkbox);
                $label.append(' ' + text);
                $li.append($label);
                $list.append($li);
                
                // Option click functionality
                $checkbox.on('change', function() {
                    var isChecked = $(this).prop('checked');
                    $option.prop('selected', isChecked);
                    updateButtonText();
                    updateSelectAll();
                    settings.onOptionClick($select, $option);
                });
                
                if (isSelected) {
                    selectedCount++;
                }
            });
            
            // Set dropdown height
            if (settings.minHeight) {
                $dropdown.css('min-height', settings.minHeight + 'px');
            }
            if (settings.maxHeight) {
                $dropdown.css('max-height', settings.maxHeight + 'px');
                $dropdown.css('overflow-y', 'auto');
            }
            
            // Append elements
            $dropdown.append($list);
            $wrapper.append($button);
            $wrapper.append($dropdown);
            $select.after($wrapper);
            
            // Button click to toggle dropdown
            $button.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $dropdown.toggle();
                $('.ms-options-dropdown').not($dropdown).hide();
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.ms-options-wrap').length) {
                    $dropdown.hide();
                }
            });
            
            // Update button text
            function updateButtonText() {
                var selected = $select.find('option:selected');
                var count = selected.length;
                var total = $options.length;
                var text = '';
                
                if (count === 0) {
                    text = settings.placeholder;
                } else if (count === total && settings.selectAll) {
                    text = '전체 선택됨';
                } else if (count === 1) {
                    text = selected.first().text();
                } else {
                    text = count + '개 선택됨';
                }
                
                $button.text(text);
            }
            
            // Update Select All checkbox state
            function updateSelectAll() {
                if (!settings.selectAll) return;
                
                var selected = $select.find('option:selected').length;
                var total = $options.length;
                var $selectAllCheckbox = $list.find('.ms-select-all input[type="checkbox"]');
                
                if (selected === total) {
                    $selectAllCheckbox.prop('checked', true);
                } else if (selected === 0) {
                    $selectAllCheckbox.prop('checked', false);
                } else {
                    $selectAllCheckbox.prop('checked', false);
                }
            }
            
            // Initial update
            updateSelectAll();
            
            // Call onLoad callback
            settings.onLoad($select);
        });
    };
})(jQuery);