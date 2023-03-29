/**
 * @file Button.component.js
 * Exports a button component.
 */

import React from 'react';
import PropTypes from 'prop-types';

/**
 * Component that renders a button with a click handler.
 */
const Example = (props) => {
  const { onClick, children } = props;
  const styles = {
    background: 'black',
    color: 'white',
    padding: '20px',
    textAlign: 'center'
  }

  return (
    <div className="example" style={styles} onClick={onClick}>
      {children}
    </div>
  );
};

Example.propTypes = {
  onClick: PropTypes.func,
  children: PropTypes.node,
};

Example.defaultProps = {
  children: null,
  onClick: () => {},
};

export default Example;
